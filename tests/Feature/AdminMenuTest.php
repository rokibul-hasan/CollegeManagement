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
