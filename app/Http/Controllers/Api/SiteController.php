<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class SiteController extends Controller
{
    /**
     * Everything the public layout needs on first load: settings, menus and notices.
     */
    public function show(): JsonResponse
    {
        $settings = Setting::allValues();
        $settings['logo_url'] = $settings['logo'] ? asset('uploads/'.$settings['logo']) : null;

        $categories = NoticeCategory::query()
            ->withCount(['notices' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug']);

        $notices = Notice::query()
            ->published()
            ->latestFirst()
            ->with('category:id,name,slug')
            ->limit(10)
            ->get(['id', 'notice_category_id', 'title', 'published_on', 'attachment', 'is_pinned', 'show_in_popup']);

        return response()->json([
            'settings' => $settings,
            'menus' => collect(Menu::LOCATIONS)->keys()->mapWithKeys(fn (string $location) => [
                $location => $this->menuTree($location, $categories),
            ]),
            'noticeCategories' => $categories,
            'notices' => $notices,
            'noticeTotal' => Notice::query()->published()->count(),
        ]);
    }

    /**
     * Active menu items for a location, nested one level deep.
     *
     * @param  Collection<int, NoticeCategory>  $categories
     * @return Collection<int, array<string, mixed>>
     */
    private function menuTree(string $location, Collection $categories): Collection
    {
        $countsBySlug = $categories->pluck('notices_count', 'slug');

        $format = fn (Menu $menu): array => [
            'id' => $menu->id,
            'label' => $menu->label,
            'url' => $menu->url,
            'style' => $menu->style ?: 'soft',
            'newTab' => $menu->open_in_new_tab,
            'count' => $this->noticeCountFor($menu->url, $countsBySlug),
        ];

        return Menu::query()
            ->where('location', $location)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->ordered()
            ->with(['children' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->map(fn (Menu $menu) => $format($menu) + [
                'children' => $menu->children->map($format)->values(),
            ]);
    }

    /**
     * Notice count for menu links pointing at the notice board, so dropdowns can show totals.
     *
     * @param  Collection<string, int>  $countsBySlug
     */
    private function noticeCountFor(?string $url, Collection $countsBySlug): ?int
    {
        if (! $url || ! str_starts_with($url, '/notices')) {
            return null;
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (empty($query['category'])) {
            return (int) $countsBySlug->sum();
        }

        return $countsBySlug->get($query['category']);
    }
}
