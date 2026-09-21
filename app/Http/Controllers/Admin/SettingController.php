<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SiteTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Current site settings.
     */
    public function show(): JsonResponse
    {
        return response()->json($this->payload());
    }

    /**
     * Save site settings, including the logo upload.
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $values = collect($request->validated())->except(['logo', 'remove_logo'])->all();

        foreach (['show_top_bar', 'notice_popup_enabled', 'video_popup_enabled'] as $flag) {
            $values[$flag] = $request->boolean($flag) ? '1' : '0';
        }

        $currentLogo = Setting::allValues()['logo'];

        if ($request->hasFile('logo')) {
            $values['logo'] = $request->file('logo')->store('logo', 'uploads');
        } elseif ($request->boolean('remove_logo')) {
            $values['logo'] = null;
        }

        if (array_key_exists('logo', $values) && $currentLogo) {
            Storage::disk('uploads')->delete($currentLogo);
        }

        Setting::store($values);

        return response()->json($this->payload());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        $settings = Setting::allValues();
        $settings['logo_url'] = $settings['logo'] ? asset('uploads/'.$settings['logo']) : null;
        $settings['template_options'] = SiteTemplate::options();

        return $settings;
    }
}
