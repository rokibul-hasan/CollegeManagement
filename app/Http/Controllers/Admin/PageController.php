<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePageRequest;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    /**
     * All pages with section counts.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Page::query()->withCount('sections')->orderBy('title')->get(),
        );
    }

    /**
     * A page with every section (including hidden ones) for the editor.
     */
    public function show(Page $page): JsonResponse
    {
        return response()->json($page->load('sections'));
    }

    /**
     * Create a page together with its sections.
     */
    public function store(SavePageRequest $request): JsonResponse
    {
        $page = DB::transaction(function () use ($request) {
            $page = Page::query()->create($this->pageAttributes($request));
            $this->syncSections($page, $this->submittedSections($request));

            return $page;
        });

        return response()->json($page->load('sections'), 201);
    }

    /**
     * Save the page details and replace its section list in the submitted order.
     */
    public function update(SavePageRequest $request, Page $page): JsonResponse
    {
        DB::transaction(function () use ($request, $page) {
            $page->update($this->pageAttributes($request));
            $this->syncSections($page, $this->submittedSections($request));
        });

        return response()->json($page->load('sections'));
    }

    /**
     * Delete a page and its sections.
     */
    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function pageAttributes(SavePageRequest $request): array
    {
        return collect($request->validated())->except('sections')->all() + [
            'is_published' => $request->boolean('is_published'),
        ];
    }

    /**
     * The validated section list. Section data is free-form (checked by SavePageRequest),
     * and validated() would strip its unlisted keys, so read it from the input instead.
     *
     * @return array<int, array{type: string, width: string, data: array<string, mixed>, is_active: bool}>
     */
    private function submittedSections(SavePageRequest $request): array
    {
        return collect($request->input('sections', []))
            ->map(fn (array $section) => [
                'type' => $section['type'],
                'width' => $section['width'],
                'data' => $section['data'] ?? [],
                'is_active' => filter_var($section['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{type: string, width: string, data: array<string, mixed>, is_active?: bool}>  $sections
     */
    private function syncSections(Page $page, array $sections): void
    {
        $page->sections()->delete();

        foreach (array_values($sections) as $position => $section) {
            $page->sections()->create([
                'type' => $section['type'],
                'width' => $section['width'],
                'data' => $section['data'],
                'is_active' => $section['is_active'] ?? true,
                'sort_order' => $position + 1,
            ]);
        }
    }
}
