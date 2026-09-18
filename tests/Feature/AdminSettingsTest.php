<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_settings_and_upload_a_logo(): void
    {
        Storage::fake('uploads');
        $admin = User::factory()->withRole('admin')->create();

        $response = $this->actingAs($admin)->post('/api/admin/settings', [
            'site_name' => 'নতুন কলেজ',
            'phone' => '০১৭০০০০০০০০',
            'show_top_bar' => '0',
            'notice_popup_enabled' => '1',
            'footer_copyright' => '© ২০২৬ নতুন কলেজ',
            'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('site_name', 'নতুন কলেজ')
            ->assertJsonPath('show_top_bar', '0');

        $settings = Setting::allValues();
        Storage::disk('uploads')->assertExists($settings['logo']);

        $this->getJson('/api/site')
            ->assertJsonPath('settings.site_name', 'নতুন কলেজ')
            ->assertJsonPath('settings.footer_copyright', '© ২০২৬ নতুন কলেজ');
    }

    public function test_site_name_is_required_and_unknown_keys_are_ignored(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/settings', ['site_name' => ''])
            ->assertJsonValidationErrors('site_name');

        Setting::store(['site_name' => 'ঠিক আছে', 'not_a_setting' => 'x']);

        $this->assertDatabaseMissing('settings', ['key' => 'not_a_setting']);
    }
}
