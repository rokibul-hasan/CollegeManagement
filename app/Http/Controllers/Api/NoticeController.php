<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    /**
     * Paginated published notices, optionally filtered by category slug or search text.
     */
    public function index(Request $request): JsonResponse
    {
        $notices = Notice::query()
            ->published()
            ->with('category:id,name,slug')
            ->when($request->string('category')->toString(), fn ($query, string $slug) => $query->whereRelation('category', 'slug', $slug))
            ->when($request->string('q')->trim()->toString(), fn ($query, string $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->latestFirst()
            ->paginate(15, ['id', 'notice_category_id', 'title', 'published_on', 'attachment', 'is_pinned']);

        return response()->json($notices);
    }

    /**
     * A single published notice.
     */
    public function show(int $id): JsonResponse
    {
        $notice = Notice::query()->published()->with('category:id,name,slug')->findOrFail($id);

        return response()->json($notice);
    }
}
