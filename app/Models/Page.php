<?php

namespace App\Models;

use App\Enums\SiteTemplate;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[Fillable(['title', 'slug', 'lead', 'meta_description', 'layout', 'is_published'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /**
     * Slugs taken by fixed routes, so pages cannot shadow them.
     *
     * @var array<int, string>
     */
    public const RESERVED_SLUGS = ['admin', 'api', 'notices', 'uploads', 'build', 'up', 'storage', 'login', 'home'];

    /**
     * The page whose sections make up the home page. It is edited from the home layout
     * screen, never from the regular page editor.
     */
    public const HOME_SLUG = 'home';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PageSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The home page record, created by migration.
     */
    public static function home(): self
    {
        return static::query()->where('slug', self::HOME_SLUG)->firstOrFail();
    }

    /**
     * Visible home sections in the order the given template shows them, in a single cached query.
     *
     * @return array<int, array{id: int, type: string, width: string, anchor: string|null, data: array<string, mixed>}>
     */
    public static function homeSections(SiteTemplate $template): array
    {
        return Cache::rememberForever(self::homeCacheKey($template), fn () => PageSection::query()
            ->select(['page_sections.id', 'page_sections.type', 'page_sections.width', 'page_sections.anchor', 'page_sections.data'])
            ->join('pages', 'pages.id', '=', 'page_sections.page_id')
            ->join('template_sections', 'template_sections.page_section_id', '=', 'page_sections.id')
            ->where('pages.slug', self::HOME_SLUG)
            ->where('page_sections.is_active', true)
            ->where('template_sections.template', $template->value)
            ->where('template_sections.is_active', true)
            ->orderBy('template_sections.sort_order')
            ->orderBy('page_sections.id')
            ->get()
            ->map(fn (PageSection $section) => $section->only(['id', 'type', 'width', 'anchor', 'data']))
            ->all());
    }

    /**
     * Drop the cached home layout of every template after home content or layout changes.
     */
    public static function forgetHomeCache(): void
    {
        foreach (SiteTemplate::cases() as $template) {
            Cache::forget(self::homeCacheKey($template));
        }
    }

    private static function homeCacheKey(SiteTemplate $template): string
    {
        return 'home_sections.'.$template->value;
    }
}
