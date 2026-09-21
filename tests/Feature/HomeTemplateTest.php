<?php

namespace Tests\Feature;

use App\Enums\SiteTemplate;
use App\Models\Page;
use App\Models\Setting;
use App\Models\TemplateSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The current admin payload's sections, in the given template's order, ready to submit back.
     *
     * @return array<int, array<string, mixed>>
     */
    private function submittable(array $payload, string $template): array
    {
        $sections = collect($payload['sections'])->keyBy('id');

        return collect($payload['layouts'][$template])
            ->map(fn (array $item) => $sections[$item['id']] + ['is_active' => $item['is_active']])
            ->values()
            ->all();
    }

    public function test_home_is_migrated_and_laid_out_for_every_template(): void
    {
        $this->getJson('/api/home')
            ->assertOk()
            ->assertJsonPath('template', 'classic')
            ->assertJsonPath('sections.0.type', 'hero')
            ->assertJsonPath('sections.1.anchor', 'itihas')
            ->assertJsonCount(13, 'sections');

        foreach (SiteTemplate::cases() as $template) {
            $this->assertSame(13, TemplateSection::query()->where('template', $template)->count());
        }
    }

    public function test_visitors_see_the_default_template_in_its_own_order_without_hidden_sections(): void
    {
        Setting::store(['site_template' => 'minimal']);

        $anchors = $this->getJson('/api/home')
            ->assertJsonPath('template', 'minimal')
            ->json('sections.*.anchor');

        $this->assertSame(['hero', 'services', 'itihas', 'bibhag', 'bharti', 'gallery'], $anchors);
    }

    public function test_home_sections_load_in_a_single_cached_query(): void
    {
        $this->getJson('/api/home')->assertOk();

        DB::enableQueryLog();
        $this->getJson('/api/home')->assertOk();

        $sectionQueries = collect(DB::getQueryLog())->filter(fn (array $query) => str_contains($query['query'], 'page_sections'));
        $this->assertCount(0, $sectionQueries);
    }

    public function test_only_page_editors_can_preview_another_template(): void
    {
        $this->getJson('/api/home?template=modern')->assertJsonPath('template', 'classic');

        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->getJson('/api/home?template=modern')
            ->assertJsonPath('template', 'modern')
            ->assertJsonPath('sections.1.anchor', 'services');
    }

    public function test_saving_reorders_one_template_and_edits_content_shared_by_all(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $payload = $this->actingAs($admin)->getJson('/api/admin/home')->assertOk()->json();

        $sections = $this->submittable($payload, 'modern');
        $sections = array_reverse($sections);
        $sections[0]['data']['heading'] = 'নতুন গ্যালারি';
        $sections[1]['is_active'] = false;

        $this->actingAs($admin)->putJson('/api/admin/home/modern', ['sections' => $sections])->assertOk();

        $modern = $this->actingAs($admin)->getJson('/api/home?template=modern')->json('sections');
        $this->assertSame('login', $modern[0]['anchor']);
        $this->assertNotContains('result', array_column($modern, 'anchor'));

        $classic = collect($this->getJson('/api/home')->json('sections'));
        $this->assertSame('hero', $classic->first()['anchor']);
        $this->assertSame('নতুন গ্যালারি', $classic->firstWhere('anchor', 'login')['data']['heading']);
    }

    public function test_added_sections_are_appended_to_other_templates_and_removed_ones_disappear_everywhere(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $payload = $this->actingAs($admin)->getJson('/api/admin/home')->json();

        $sections = collect($this->submittable($payload, 'classic'))
            ->reject(fn (array $section) => $section['anchor'] === 'gallery')
            ->prepend(['type' => 'text', 'width' => 'full', 'anchor' => 'welcome', 'is_active' => true, 'data' => ['heading' => 'স্বাগতম']])
            ->values()
            ->all();

        $this->actingAs($admin)->putJson('/api/admin/home/classic', ['sections' => $sections])->assertOk();

        $classic = $this->getJson('/api/home')->json('sections.*.anchor');
        $this->assertSame('welcome', $classic[0]);
        $this->assertNotContains('gallery', $classic);

        $modern = $this->actingAs($admin)->getJson('/api/home?template=modern')->json('sections.*.anchor');
        $this->assertSame('welcome', end($modern));
        $this->assertNotContains('gallery', $modern);
    }

    public function test_reset_restores_the_template_preset(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $payload = $this->actingAs($admin)->getJson('/api/admin/home')->json();

        $this->actingAs($admin)->putJson('/api/admin/home/classic', [
            'sections' => array_reverse($this->submittable($payload, 'classic')),
        ])->assertOk();

        $this->actingAs($admin)->postJson('/api/admin/home/classic/reset')
            ->assertOk()
            ->assertJsonPath('layouts.classic.0.id', collect($payload['sections'])->firstWhere('anchor', 'hero')['id']);

        $this->assertSame('hero', $this->getJson('/api/home')->json('sections.0.anchor'));
    }

    public function test_admin_can_make_a_template_the_default(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->postJson('/api/admin/home/elegant/default')->assertOk();

        $this->assertSame(SiteTemplate::Elegant, Setting::template());
        $this->getJson('/api/site')->assertJsonPath('settings.site_template', 'elegant');
        $this->getJson('/api/home')->assertJsonPath('template', 'elegant');
    }

    public function test_admin_can_choose_the_template_from_site_settings(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->getJson('/api/admin/settings')
            ->assertJsonPath('site_template', 'classic')
            ->assertJsonCount(4, 'template_options');

        $this->actingAs($admin)->postJson('/api/admin/settings', ['site_name' => 'কলেজ', 'site_template' => 'modern'])
            ->assertOk()
            ->assertJsonPath('site_template', 'modern');

        $this->getJson('/api/home')->assertJsonPath('template', 'modern');
    }

    public function test_unknown_templates_and_foreign_sections_are_rejected(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $aboutSection = Page::query()->where('slug', 'about')->firstOrFail()->sections()->first();

        $this->actingAs($admin)->postJson('/api/admin/home/neon/default')->assertNotFound();

        $this->actingAs($admin)->putJson('/api/admin/home/classic', ['sections' => [
            ['id' => $aboutSection->id, 'type' => 'text', 'width' => 'full', 'data' => []],
        ]])->assertJsonValidationErrors('sections.0.id');

        $this->actingAs($admin)->postJson('/api/admin/settings', ['site_name' => 'কলেজ', 'site_template' => 'neon'])
            ->assertJsonValidationErrors('site_template');
    }

    public function test_home_page_is_hidden_from_the_page_editor_and_public_page_route(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $home = Page::home();

        $this->getJson('/api/pages/home')->assertNotFound();
        $this->actingAs($admin)->getJson("/api/admin/pages/{$home->id}")->assertNotFound();
        $this->actingAs($admin)->deleteJson("/api/admin/pages/{$home->id}")->assertNotFound();
        $this->assertNotContains('home', $this->actingAs($admin)->getJson('/api/admin/pages')->json('*.slug'));
    }

    public function test_users_without_permission_cannot_change_the_home_layout(): void
    {
        $editor = User::factory()->withRole('editor')->create();

        $this->actingAs($editor)->getJson('/api/admin/home')->assertForbidden();
        $this->actingAs($editor)->postJson('/api/admin/home/modern/default')->assertForbidden();
    }
}
