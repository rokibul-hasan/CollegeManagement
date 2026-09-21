<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'লাইব্রেরি',
            'slug' => 'library',
            'lead' => 'বই ও পাঠকক্ষ',
            'layout' => 'full',
            'is_published' => true,
            'sections' => [
                ['type' => 'text', 'width' => 'full', 'is_active' => true, 'data' => ['heading' => 'পরিচিতি', 'body' => 'লেখা']],
                ['type' => 'html', 'width' => 'half', 'is_active' => true, 'data' => ['html' => '<div class="card">HTML</div>']],
                ['type' => 'widget', 'width' => 'half', 'is_active' => false, 'data' => ['widget' => 'contact_info']],
            ],
        ], $overrides);
    }

    public function test_default_pages_are_created_by_migration(): void
    {
        foreach (['about', 'teachers', 'departments', 'routine', 'admission-fee', 'form-fee', 'result', 'student-login', 'co-curricular', 'contact'] as $slug) {
            $this->getJson("/api/pages/{$slug}")->assertOk()->assertJsonPath('slug', $slug);
        }
    }

    public function test_admin_can_create_a_page_and_public_sees_only_active_sections(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload())->assertCreated();

        $this->getJson('/api/pages/library')
            ->assertOk()
            ->assertJsonCount(2, 'sections')
            ->assertJsonPath('sections.0.type', 'text')
            ->assertJsonPath('sections.1.width', 'half')
            ->assertJsonPath('sections.1.data.html', '<div class="card">HTML</div>');
    }

    public function test_update_replaces_sections_in_the_submitted_order(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $id = $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload())->json('id');

        $this->actingAs($admin)->putJson("/api/admin/pages/{$id}", $this->payload([
            'sections' => [
                ['type' => 'gallery', 'width' => 'full', 'is_active' => true, 'data' => ['heading' => 'ছবি', 'items' => []]],
                ['type' => 'text', 'width' => 'full', 'is_active' => true, 'data' => ['heading' => 'দ্বিতীয়']],
            ],
        ]))->assertOk();

        $this->assertSame(['gallery', 'text'], Page::query()->find($id)->sections->pluck('type')->all());
    }

    public function test_drafts_are_hidden_but_page_editors_can_preview_them(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload(['is_published' => false]))->assertCreated();

        $this->actingAs($admin)->getJson('/api/pages/library?preview=1')->assertOk()->assertJsonPath('is_published', false);

        auth()->logout();
        $this->getJson('/api/pages/library')->assertNotFound();
        $this->getJson('/api/pages/library?preview=1')->assertNotFound();
    }

    public function test_reserved_slugs_and_unsafe_links_are_rejected(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload(['slug' => 'admin']))
            ->assertJsonValidationErrors('slug');

        $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload([
            'sections' => [['type' => 'cards', 'width' => 'full', 'data' => ['items' => [['title' => 'x', 'url' => 'javascript:alert(1)']]]]],
        ]))->assertJsonValidationErrors('sections.0.data');

        $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload([
            'sections' => [['type' => 'bogus', 'width' => 'full', 'data' => []]],
        ]))->assertJsonValidationErrors('sections.0.type');
    }

    public function test_widget_sections_carry_their_own_labels_and_target_link(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload([
            'sections' => [[
                'type' => 'widget', 'width' => 'full', 'is_active' => true,
                'data' => [
                    'widget' => 'result_form',
                    'heading' => 'এইচএসসি ফলাফল',
                    'button_label' => 'দেখুন',
                    'action_url' => 'https://educationboardresults.gov.bd/',
                ],
            ]],
        ]))->assertCreated();

        $this->getJson('/api/pages/library')
            ->assertJsonPath('sections.0.data.heading', 'এইচএসসি ফলাফল')
            ->assertJsonPath('sections.0.data.action_url', 'https://educationboardresults.gov.bd/');
    }

    public function test_a_widget_target_link_must_be_safe(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/pages', $this->payload([
            'sections' => [[
                'type' => 'widget', 'width' => 'full', 'is_active' => true,
                'data' => ['widget' => 'result_form', 'action_url' => 'javascript:alert(1)'],
            ]],
        ]))->assertJsonValidationErrors('sections.0.data');
    }

    public function test_editors_without_page_permission_are_blocked(): void
    {
        $editor = User::factory()->withRole('editor')->create();

        $this->actingAs($editor)->getJson('/api/admin/pages')->assertForbidden();
        $this->actingAs($editor)->postJson('/api/admin/pages', $this->payload())->assertForbidden();
    }

    public function test_images_can_be_uploaded_for_sections(): void
    {
        Storage::fake('uploads');
        $admin = User::factory()->withRole('admin')->create();

        $url = $this->actingAs($admin)->post('/api/admin/media', [
            'image' => UploadedFile::fake()->image('photo.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated()->json('url');

        Storage::disk('uploads')->assertExists(str_replace('/uploads/', '', $url));

        $this->actingAs($admin)->post('/api/admin/media', [
            'image' => UploadedFile::fake()->create('shell.php', 1, 'text/x-php'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }
}
