<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

/**
 * Swaps the stock photos the site shipped with for the college's own campus
 * pictures, wherever a section references them.
 */
class CampusPhotoSeeder extends Seeder
{
    private const ASSEMBLY = '/uploads/campus/campus-assembly.jpg';

    private const BUILDING = '/uploads/campus/campus-building.jpg';

    private const ASSEMBLY_SMALL = '/uploads/campus/campus-assembly-wide.jpg';

    private const BUILDING_SMALL = '/uploads/campus/campus-building-wide.jpg';

    /**
     * The placeholder image URLs the first release seeded, and what replaces them.
     *
     * @var array<string, string>
     */
    private const REPLACEMENTS = [
        'https://www.bssnews.net/bangla/assets/news_photos/2024/01/26/image-123842-1706246196.jpg' => self::ASSEMBLY,
        'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPUQDVfddw8QvKH7togTst3dWCtiAcTHW6NVBmRbLKthrL6ddDy75D285o&s=10' => self::BUILDING,
    ];

    /**
     * Captions for the gallery sections, which previously repeated two stock images.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const GALLERY = [
        [self::BUILDING_SMALL, 'কলেজ ভবন'],
        [self::ASSEMBLY_SMALL, 'শিক্ষার্থীদের সমাবেশ'],
        [self::ASSEMBLY_SMALL, 'বার্ষিক ক্রীড়া'],
        [self::BUILDING_SMALL, 'কলেজ প্রাঙ্গণ'],
    ];

    /**
     * The hero slider, which used to repeat one stock photo three times.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const SLIDES = [
        [self::BUILDING, 'গোপালগঞ্জ সরকারি মহিলা কলেজের মূল ভবন'],
        [self::ASSEMBLY, 'কলেজ প্রাঙ্গণে শিক্ষার্থীদের সমাবেশ'],
    ];

    /**
     * Point every section at the real photos.
     */
    public function run(): void
    {
        $changed = 0;

        foreach (PageSection::query()->cursor() as $section) {
            $data = $section->data ?? [];
            $updated = match ($section->type) {
                'gallery' => $this->withGallery($data),
                'hero' => $this->withSlides($data),
                default => $this->replaceUrls($data),
            };

            if ($updated !== $data) {
                $section->update(['data' => $updated]);
                $changed++;
            }
        }

        Page::forgetHomeCache();

        $this->command?->info("{$changed}টি সেকশনে কলেজের নিজস্ব ছবি বসানো হয়েছে।");
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withGallery(array $data): array
    {
        $data['items'] = array_map(
            fn (array $item) => ['image' => $item[0], 'caption' => $item[1]],
            self::GALLERY,
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withSlides(array $data): array
    {
        $data['slides'] = array_map(
            fn (array $slide) => ['image' => $slide[0], 'alt' => $slide[1]],
            self::SLIDES,
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function replaceUrls(array $data): array
    {
        array_walk_recursive($data, function (mixed &$value): void {
            if (is_string($value) && array_key_exists($value, self::REPLACEMENTS)) {
                $value = self::REPLACEMENTS[$value];
            }
        });

        return $data;
    }
}
