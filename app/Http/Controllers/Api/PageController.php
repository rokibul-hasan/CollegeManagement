<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * A published page with its active sections. Page editors may preview drafts with ?preview=1.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $canPreview = $request->boolean('preview') && $request->user()?->can('pages.manage');

        $page = Page::query()
            ->where('slug', $slug)
            ->unless($canPreview, fn ($query) => $query->where('is_published', true))
            ->with(['sections' => fn ($query) => $query->where('is_active', true)])
            ->firstOrFail();

        return response()->json([
            'title' => $page->title,
            'slug' => $page->slug,
            'lead' => $page->lead,
            'meta_description' => $page->meta_description,
            'layout' => $page->layout,
            'is_published' => $page->is_published,
            'sections' => $page->sections->map(fn (PageSection $section) => $section->only(['id', 'type', 'width', 'data'])),
        ]);
    }
}
