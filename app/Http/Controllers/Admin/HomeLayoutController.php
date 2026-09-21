<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SiteTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveHomeLayoutRequest;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Setting;
use App\Models\TemplateSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomeLayoutController extends Controller
{
    /**
     * Home content shared by all templates, plus each template's section order and visibility.
     */
    public function show(): JsonResponse
    {
        return response()->json($this->payload());
    }

    /**
     * Save the home content and the given template's layout. The submitted order becomes
     * this template's order; sections added here are appended to every other template.
     */
    public function update(SaveHomeLayoutRequest $request, SiteTemplate $template): JsonResponse
    {
        DB::transaction(function () use ($request, $template) {
            $home = Page::home();
            $submitted = $request->sections();

            $home->sections()
                ->whereNotIn('id', array_filter(array_column($submitted, 'id')))
                ->delete();

            $this->updateExistingSections($home, $submitted);
            $newIds = $this->createNewSections($home, $submitted);

            $orderedIds = array_map(
                fn (array $section, int $index) => $section['id'] ?? $newIds[$index],
                $submitted,
                array_keys($submitted),
            );

            TemplateSection::query()->upsert(
                array_map(fn (int $id, int $position) => [
                    'template' => $template->value,
                    'page_section_id' => $id,
                    'sort_order' => $position + 1,
                    'is_active' => $submitted[$position]['is_active'],
                ], $orderedIds, array_keys($orderedIds)),
                ['template', 'page_section_id'],
                ['sort_order', 'is_active'],
            );

            $this->appendToOtherTemplates($template, array_values($newIds));
        });

        Page::forgetHomeCache();

        return response()->json($this->payload());
    }

    /**
     * Restore the template's built-in section order and visibility.
     */
    public function reset(SiteTemplate $template): JsonResponse
    {
        $sections = Page::home()->sections()->get(['id', 'page_id', 'anchor', 'sort_order']);
        $preset = array_flip($template->presetOrder());

        $ordered = $sections->sortBy(fn (PageSection $section) => [
            $preset[$section->anchor] ?? PHP_INT_MAX,
            $section->sort_order,
            $section->id,
        ])->values();

        TemplateSection::query()->upsert(
            $ordered->map(fn (PageSection $section, int $position) => [
                'template' => $template->value,
                'page_section_id' => $section->id,
                'sort_order' => $position + 1,
                'is_active' => ! in_array($section->anchor, $template->presetHidden(), true),
            ])->all(),
            ['template', 'page_section_id'],
            ['sort_order', 'is_active'],
        );

        Page::forgetHomeCache();

        return response()->json($this->payload());
    }

    /**
     * Make the template the one visitors see.
     */
    public function setDefault(SiteTemplate $template): JsonResponse
    {
        Setting::store(['site_template' => $template->value]);

        return response()->json(['default_template' => $template->value]);
    }

    /**
     * Update every submitted section that already exists in one bulk statement.
     *
     * @param  array<int, array{id: int|null, type: string, width: string, anchor: string|null, data: array<string, mixed>, is_active: bool}>  $submitted
     */
    private function updateExistingSections(Page $home, array $submitted): void
    {
        $rows = collect($submitted)
            ->filter(fn (array $section) => $section['id'] !== null)
            ->map(fn (array $section, int $position) => [
                'id' => $section['id'],
                'page_id' => $home->id,
                'type' => $section['type'],
                'width' => $section['width'],
                'anchor' => $section['anchor'],
                'data' => json_encode($section['data'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'sort_order' => $position + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if ($rows !== []) {
            PageSection::query()->upsert($rows, ['id'], ['type', 'width', 'anchor', 'data', 'sort_order', 'updated_at']);
        }
    }

    /**
     * Create the submitted sections that have no id yet.
     *
     * @param  array<int, array{id: int|null, type: string, width: string, anchor: string|null, data: array<string, mixed>, is_active: bool}>  $submitted
     * @return array<int, int> New section ids keyed by their position in the submitted list.
     */
    private function createNewSections(Page $home, array $submitted): array
    {
        return collect($submitted)
            ->filter(fn (array $section) => $section['id'] === null)
            ->map(fn (array $section, int $position) => $home->sections()->create([
                'type' => $section['type'],
                'width' => $section['width'],
                'anchor' => $section['anchor'],
                'data' => $section['data'],
                'is_active' => true,
                'sort_order' => $position + 1,
            ])->id)
            ->all();
    }

    /**
     * Show newly added sections at the end of every other template, so no content goes missing.
     *
     * @param  array<int, int>  $sectionIds
     */
    private function appendToOtherTemplates(SiteTemplate $current, array $sectionIds): void
    {
        if ($sectionIds === []) {
            return;
        }

        $others = array_values(array_diff(SiteTemplate::values(), [$current->value]));

        $lastPositions = TemplateSection::query()
            ->whereIn('template', $others)
            ->groupBy('template')
            ->selectRaw('template, max(sort_order) as last_position')
            ->pluck('last_position', 'template');

        $rows = [];

        foreach ($others as $template) {
            $position = (int) $lastPositions->get($template, 0);

            foreach ($sectionIds as $id) {
                $rows[] = ['template' => $template, 'page_section_id' => $id, 'sort_order' => ++$position, 'is_active' => true];
            }
        }

        TemplateSection::query()->insert($rows);
    }

    /**
     * @return array{default_template: string, templates: array<int, array<string, string>>, sections: Collection<int, array<string, mixed>>, layouts: array<string, array<int, array{id: int, is_active: bool}>>}
     */
    private function payload(): array
    {
        $sections = Page::home()
            ->sections()
            ->with('templateSections:id,page_section_id,template,sort_order,is_active')
            ->get(['id', 'page_id', 'type', 'width', 'anchor', 'data', 'sort_order']);

        $layouts = [];

        foreach (SiteTemplate::cases() as $template) {
            $layouts[$template->value] = $sections
                ->map(function (PageSection $section) use ($template) {
                    $placement = $section->templateSections->firstWhere('template', $template);

                    return [
                        'id' => $section->id,
                        'is_active' => $placement?->is_active ?? false,
                        'position' => $placement?->sort_order ?? PHP_INT_MAX,
                    ];
                })
                ->sortBy([['position', 'asc'], ['id', 'asc']])
                ->map(fn (array $item) => ['id' => $item['id'], 'is_active' => $item['is_active']])
                ->values()
                ->all();
        }

        return [
            'default_template' => Setting::template()->value,
            'templates' => SiteTemplate::options(),
            'sections' => $sections->map(fn (PageSection $section) => $section->only(['id', 'type', 'width', 'anchor', 'data']))->values(),
            'layouts' => $layouts,
        ];
    }
}
