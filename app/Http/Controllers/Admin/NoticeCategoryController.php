<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNoticeCategoryRequest;
use App\Models\NoticeCategory;
use Illuminate\Http\JsonResponse;

class NoticeCategoryController extends Controller
{
    /**
     * All categories with notice counts.
     */
    public function index(): JsonResponse
    {
        return response()->json(NoticeCategory::query()->withCount('notices')->orderBy('sort_order')->get());
    }

    /**
     * Create a category.
     */
    public function store(StoreNoticeCategoryRequest $request): JsonResponse
    {
        return response()->json(NoticeCategory::query()->create($request->validated()), 201);
    }

    /**
     * Update a category.
     */
    public function update(StoreNoticeCategoryRequest $request, NoticeCategory $noticeCategory): JsonResponse
    {
        $noticeCategory->update($request->validated());

        return response()->json($noticeCategory);
    }

    /**
     * Delete a category; its notices become uncategorised.
     */
    public function destroy(NoticeCategory $noticeCategory): JsonResponse
    {
        $noticeCategory->delete();

        return response()->json(['ok' => true]);
    }
}
