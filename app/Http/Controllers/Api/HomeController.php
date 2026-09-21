<?php

namespace App\Http\Controllers\Api;

use App\Enums\SiteTemplate;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Home sections for the active template. Page editors may preview another template with ?template=.
     */
    public function show(Request $request): JsonResponse
    {
        $preview = $request->user()?->can('pages.manage')
            ? SiteTemplate::tryFrom($request->string('template')->toString())
            : null;

        $template = $preview ?? Setting::template();

        return response()->json([
            'template' => $template->value,
            'sections' => Page::homeSections($template),
        ]);
    }
}
