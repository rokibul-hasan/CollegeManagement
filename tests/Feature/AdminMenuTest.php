<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMenuTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->withRole('admin')->create();
    }

    public function test_admin_can_add_child_menu_items_appended_to_the_end(): void
    {
        $parent = Menu::factory()->create(['location' => 'footer', 'url' => null]);
        Menu::factory()->create(['location' => 'footer', 'parent_id' => $parent->id, 'sort_order' => 4]);

        $this->actingAs($this->admin)->postJson('/api/admin/menus', [
            'location' => 'footer',
            'parent_id' => $parent->id,
            'label' => 'ব্যানবেইস',
            'url' => 'https://banbeis.gov.bd/',
            'open_in_new_tab' => true,
        ])->assertCreated()->assertJsonPath('sort_order', 5);
    }

    public function test_header_buttons_keep_their_style_and_reach_the_public_payload(): void
    {
        Menu::query()->whereIn('location', ['topbar', 'header'])->delete();

        $this->actingAs($this->admin)->postJson('/api/admin/menus', [
            'location' => 'header',
            'label' => 'ভর্তি তথ্য',
            'url' => '/admission-fee',
            'style' => 'primary',
        ])->assertCreated()->assertJsonPath('style', 'primary');

        Menu::factory()->create(['location' => 'topbar', 'label' => 'লগইন', 'url' => '/student-login']);

        $this->getJson('/api/site')
            ->assertJsonPath('menus.header.0.style', 'primary')
            ->assertJsonPath('menus.header.0.label', 'ভর্তি তথ্য')
            ->assertJsonPath('menus.topbar.0.label', 'লগইন')
            ->assertJsonPath('menus.topbar.0.style', 'soft');
    }

    public function test_the_top_bar_and_header_keep_their_previous_links_after_migrating(): void
    {
        $this->getJson('/api/site')
            ->assertJsonPath('menus.topbar.0.url', '/admission-fee')
            ->assertJsonPath('menus.header.0.style', 'soft-live')
            ->assertJsonPath('menus.header.1.style', 'primary');
    }

    public function test_an_unknown_button_style_is_rejected(): void
    {
        $this->actingAs($this->admin)->postJson('/api/admin/menus', [
            'location' => 'header',
            'label' => 'বাটন',
            'url' => '/notices',
            'style' => 'rainbow',
        ])->assertJsonValidationErrors('style');
    }

    public function test_menu_urls_must_be_safe(): void
    {
        $this->actingAs($this->admin)->postJson('/api/admin/menus', [
            'location' => 'main',
            'label' => 'খারাপ লিংক',
            'url' => 'javascript:alert(1)',
        ])->assertJsonValidationErrors('url');
    }

    public function test_parent_must_be_a_top_level_item_in_the_same_location(): void
    {
        $footerParent = Menu::factory()->create(['location' => 'footer']);

        $this->actingAs($this->admin)->postJson('/api/admin/menus', [
            'location' => 'main',
            'parent_id' => $footerParent->id,
            'label' => 'ভুল অবস্থান',
        ])->assertJsonValidationErrors('parent_id');
    }

    public function test_reorder_and_delete_cascades_to_children(): void
    {
        $first = Menu::factory()->create(['sort_order' => 1]);
        $second = Menu::factory()->create(['sort_order' => 2]);
        $child = Menu::factory()->create(['parent_id' => $second->id]);

        $this->actingAs($this->admin)->postJson('/api/admin/menus/reorder', ['ids' => [$second->id, $first->id]])->assertOk();

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);

        $this->actingAs($this->admin)->deleteJson("/api/admin/menus/{$second->id}")->assertOk();

        $this->assertModelMissing($child);
    }
}
