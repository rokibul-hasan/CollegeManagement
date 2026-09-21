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

    public function test_admin_can_rename_the_fixed_labels_shown_on_the_site(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/settings', [
            'site_name' => 'কলেজ',
            'ticker_label' => 'ব্রেকিং',
            'ticker_link_label' => 'সব খবর',
            'ticker_link_url' => '/notices?category=admission',
            'notice_board_title' => 'বিজ্ঞপ্তি বোর্ড',
            'quick_links_title' => 'দ্রুত লিংক',
            'notice_popup_kicker' => 'গুরুত্বপূর্ণ',
            'instagram_url' => 'https://instagram.com/college',
        ])->assertOk();

        $this->getJson('/api/site')
            ->assertJsonPath('settings.ticker_label', 'ব্রেকিং')
            ->assertJsonPath('settings.ticker_link_url', '/notices?category=admission')
            ->assertJsonPath('settings.notice_board_title', 'বিজ্ঞপ্তি বোর্ড')
            ->assertJsonPath('settings.quick_links_title', 'দ্রুত লিংক')
            ->assertJsonPath('settings.notice_popup_kicker', 'গুরুত্বপূর্ণ')
            ->assertJsonPath('settings.instagram_url', 'https://instagram.com/college');
    }

    public function test_label_links_must_use_a_safe_scheme(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/settings', [
            'site_name' => 'কলেজ',
            'ticker_link_url' => 'javascript:alert(1)',
        ])->assertJsonValidationErrors('ticker_link_url');
    }

    public function test_admin_can_enable_the_youtube_video_popup(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/settings', [
            'site_name' => 'কলেজ',
            'video_popup_enabled' => true,
            'video_popup_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'video_popup_title' => 'ক্যাম্পাস ট্যুর',
        ])->assertOk();

        $this->getJson('/api/site')
            ->assertJsonPath('settings.video_popup_enabled', '1')
            ->assertJsonPath('settings.video_popup_url', 'https://youtu.be/dQw4w9WgXcQ')
            ->assertJsonPath('settings.video_popup_title', 'ক্যাম্পাস ট্যুর');
    }

    public function test_video_popup_needs_a_youtube_link_when_enabled(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/settings', ['site_name' => 'কলেজ', 'video_popup_enabled' => true])
            ->assertJsonValidationErrors('video_popup_url');

        $this->actingAs($admin)->postJson('/api/admin/settings', [
            'site_name' => 'কলেজ',
            'video_popup_enabled' => true,
            'video_popup_url' => 'https://vimeo.com/123456',
        ])->assertJsonValidationErrors('video_popup_url');
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
