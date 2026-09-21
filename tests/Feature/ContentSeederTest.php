<?php

namespace Tests\Feature;

use App\Models\PageSection;
use Database\Seeders\CampusPhotoSeeder;
use Database\Seeders\StaffSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_staff_seeder_fills_the_teachers_page_from_the_register(): void
    {
        $this->seed(StaffSeeder::class);

        $response = $this->getJson('/api/pages/teachers')->assertOk();

        $headings = collect($response->json('sections'))->pluck('data.heading');

        $this->assertContains('বাংলা বিভাগ', $headings);
        $this->assertContains('কর্মকর্তা ও কর্মচারীবৃন্দ', $headings);
        $this->assertContains('হোস্টেল কর্মচারীবৃন্দ', $headings);

        $everyone = collect($response->json('sections'))->flatMap(fn (array $section) => $section['data']['items'] ?? []);

        $this->assertContains('Prof. Sheikh Benozir Ahemed', $everyone->pluck('name'));
        $this->assertContains('Nihar Ranjan Kanzilal', $everyone->pluck('name'));
        $this->assertContains('জ্যোৎস্না বৈদ্য', $everyone->pluck('name'));
        $this->assertSame(69, $everyone->count());
    }

    public function test_every_portrait_the_seeder_references_exists(): void
    {
        $this->seed(StaffSeeder::class);

        $photos = collect($this->getJson('/api/pages/teachers')->json('sections'))
            ->flatMap(fn (array $section) => $section['data']['items'] ?? [])
            ->pluck('photo')
            ->filter()
            ->unique();

        $this->assertCount(67, $photos);

        foreach ($photos as $photo) {
            $this->assertFileExists(public_path(ltrim($photo, '/')));
        }
    }

    public function test_the_principal_is_named_on_the_home_page(): void
    {
        $this->seed(StaffSeeder::class);

        $messages = PageSection::query()->where('type', 'intro')->firstOrFail()->data['messages'];

        $this->assertSame('Prof. Sheikh Benozir Ahemed', $messages[0]['name']);
        $this->assertSame('/uploads/staff/photo-01.jpg', $messages[0]['photo']);
    }

    public function test_the_campus_photo_seeder_replaces_every_stock_image(): void
    {
        $this->seed(CampusPhotoSeeder::class);

        $images = PageSection::query()
            ->pluck('data')
            ->map(fn (array $data) => json_encode($data, JSON_UNESCAPED_SLASHES))
            ->implode(' ');

        $this->assertStringNotContainsString('bssnews.net', $images);
        $this->assertStringNotContainsString('gstatic.com', $images);
        $this->assertStringContainsString('/uploads/campus/', $images);

        $slides = PageSection::query()->where('type', 'hero')->firstOrFail()->data['slides'];

        foreach ($slides as $slide) {
            $this->assertStringStartsWith('/uploads/campus/', $slide['image']);
        }
    }
}
