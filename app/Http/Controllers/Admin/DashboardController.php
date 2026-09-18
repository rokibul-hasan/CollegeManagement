<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Summary counts and recent notices for the admin home.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'notices' => Notice::query()->count(),
                'published' => Notice::query()->published()->count(),
                'popup' => Notice::query()->published()->where('show_in_popup', true)->count(),
                'categories' => NoticeCategory::query()->count(),
                'menus' => Menu::query()->count(),
            ],
            'recentNotices' => Notice::query()->with('category:id,name')->latest()->limit(6)->get(),
        ]);
    }
}
