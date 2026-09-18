<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminNoticeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        $this->admin = User::factory()->withRole('admin')->create();
    }

    public function test_admin_can_create_a_notice_with_an_attachment(): void
    {
        $category = NoticeCategory::factory()->create();

        $response = $this->actingAs($this->admin)->post('/api/admin/notices', [
            'title' => 'নতুন ভর্তি বিজ্ঞপ্তি',
            'notice_category_id' => $category->id,
            'body' => 'বিস্তারিত',
            'published_on' => '2026-09-01',
            'is_published' => '1',
            'show_in_popup' => '1',
            'is_pinned' => '0',
            'attachment' => UploadedFile::fake()->create('notice.pdf', 200, 'application/pdf'),
        ], ['Accept' => 'application/json']);

        $response->assertCreated();

        $notice = Notice::query()->sole();
        $this->assertTrue($notice->show_in_popup);
        $this->assertFalse($notice->is_pinned);
        Storage::disk('uploads')->assertExists($notice->attachment);
    }

    public function test_admin_can_update_via_method_spoofing_and_remove_attachment(): void
    {
        Storage::disk('uploads')->put('notices/old.pdf', 'x');
        $notice = Notice::factory()->create(['attachment' => 'notices/old.pdf']);

        $this->actingAs($this->admin)->post("/api/admin/notices/{$notice->id}", [
            '_method' => 'PUT',
            'title' => 'হালনাগাদ শিরোনাম',
            'published_on' => '2026-09-02',
            'is_published' => '0',
            'remove_attachment' => '1',
        ], ['Accept' => 'application/json'])->assertOk();

        $notice->refresh();
        $this->assertSame('হালনাগাদ শিরোনাম', $notice->title);
        $this->assertFalse($notice->is_published);
        $this->assertNull($notice->attachment);
        Storage::disk('uploads')->assertMissing('notices/old.pdf');
    }

    public function test_notice_requires_title_and_date_and_rejects_executable_uploads(): void
    {
        $this->actingAs($this->admin)->post('/api/admin/notices', [
            'attachment' => UploadedFile::fake()->create('evil.php', 10, 'text/x-php'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'published_on', 'attachment']);
    }

    public function test_admin_can_delete_a_notice(): void
    {
        $notice = Notice::factory()->create();

        $this->actingAs($this->admin)->deleteJson("/api/admin/notices/{$notice->id}")->assertOk();

        $this->assertModelMissing($notice);
    }
}
