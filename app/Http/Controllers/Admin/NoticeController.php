<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNoticeRequest;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    /**
     * Paginated notices for the admin list, including drafts.
     */
    public function index(Request $request): JsonResponse
    {
        $notices = Notice::query()
            ->with('category:id,name')
            ->when($request->integer('category'), fn ($query, int $categoryId) => $query->where('notice_category_id', $categoryId))
            ->when($request->string('q')->trim()->toString(), fn ($query, string $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderByDesc('published_on')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($notices);
    }

    /**
     * A single notice for editing.
     */
    public function show(Notice $notice): JsonResponse
    {
        return response()->json($notice);
    }

    /**
     * Create a notice.
     */
    public function store(StoreNoticeRequest $request): JsonResponse
    {
        $notice = new Notice;
        $this->fillAndSave($notice, $request);

        return response()->json($notice, 201);
    }

    /**
     * Update a notice.
     */
    public function update(StoreNoticeRequest $request, Notice $notice): JsonResponse
    {
        $this->fillAndSave($notice, $request);

        return response()->json($notice);
    }

    /**
     * Delete a notice and its attachment.
     */
    public function destroy(Notice $notice): JsonResponse
    {
        if ($notice->attachment) {
            Storage::disk('uploads')->delete($notice->attachment);
        }

        $notice->delete();

        return response()->json(['ok' => true]);
    }

    private function fillAndSave(Notice $notice, StoreNoticeRequest $request): void
    {
        $notice->fill(collect($request->validated())->except(['attachment', 'remove_attachment'])->all());

        foreach (['is_published', 'show_in_popup', 'is_pinned'] as $flag) {
            $notice->{$flag} = $request->boolean($flag);
        }

        $replacingAttachment = $request->hasFile('attachment') || $request->boolean('remove_attachment');

        if ($replacingAttachment && $notice->attachment) {
            Storage::disk('uploads')->delete($notice->attachment);
            $notice->attachment = null;
        }

        if ($request->hasFile('attachment')) {
            $notice->attachment = $request->file('attachment')->store('notices', 'uploads');
        }

        $notice->save();
    }
}
