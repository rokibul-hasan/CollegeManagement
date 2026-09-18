<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_spa_shell_is_served_for_site_and_admin_paths(): void
    {
        $this->get('/')->assertOk()->assertSee('id="app"', false);
        $this->get('/notices/5')->assertOk();
        $this->get('/admin/menus')->assertOk();
    }

    public function test_site_payload_contains_settings_menus_and_published_notices_only(): void
    {
        $category = NoticeCategory::factory()->create(['slug' => 'exam']);
        $parent = Menu::factory()->create(['label' => 'নোটিশ', 'url' => '/notices']);
        Menu::factory()->create(['parent_id' => $parent->id, 'label' => 'পরীক্ষা', 'url' => '/notices?category=exam']);
        Menu::factory()->create(['label' => 'লুকানো', 'is_active' => false]);

        Notice::factory()->for($category, 'category')->create(['title' => 'প্রকাশিত নোটিশ', 'show_in_popup' => true]);
        Notice::factory()->create(['title' => 'খসড়া নোটিশ', 'is_published' => false]);
        Notice::factory()->create(['title' => 'ভবিষ্যতের নোটিশ', 'published_on' => now()->addWeek()->toDateString()]);

        $response = $this->getJson('/api/site')->assertOk();

        $response->assertJsonPath('settings.site_name', 'গোপালগঞ্জ সরকারি মহিলা কলেজ')
            ->assertJsonPath('noticeTotal', 1)
            ->assertJsonCount(1, 'notices')
            ->assertJsonPath('notices.0.title', 'প্রকাশিত নোটিশ')
            ->assertJsonCount(1, 'menus.main')
            ->assertJsonPath('menus.main.0.count', 1)
            ->assertJsonPath('menus.main.0.children.0.count', 1);
    }

    public function test_notices_can_be_filtered_by_category_and_search(): void
    {
        $exam = NoticeCategory::factory()->create(['slug' => 'exam']);
        Notice::factory()->for($exam, 'category')->create(['title' => 'টেস্ট পরীক্ষার রুটিন']);
        Notice::factory()->create(['title' => 'ভর্তি বিজ্ঞপ্তি']);

        $this->getJson('/api/notices?category=exam')->assertOk()->assertJsonPath('total', 1);
        $this->getJson('/api/notices?q=ভর্তি')->assertOk()->assertJsonPath('data.0.title', 'ভর্তি বিজ্ঞপ্তি');
    }

    public function test_unpublished_notice_is_not_publicly_visible(): void
    {
        $draft = Notice::factory()->create(['is_published' => false]);

        $this->getJson("/api/notices/{$draft->id}")->assertNotFound();
    }
}
