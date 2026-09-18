<?php

namespace Database\Seeders;

use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    /**
     * Seed notice categories and the sample notices from the design.
     */
    public function run(): void
    {
        $categories = collect([
            ['ভর্তি বিজ্ঞপ্তি', 'admission'],
            ['পরীক্ষা ও রুটিন', 'exam'],
            ['ফলাফল', 'result'],
            ['প্রশাসনিক নোটিশ', 'administrative'],
            ['টেন্ডার ও নিয়োগ', 'tender'],
            ['একাডেমিক', 'academic'],
        ])->mapWithKeys(fn (array $category, int $position) => [
            $category[1] => NoticeCategory::query()->updateOrCreate(
                ['slug' => $category[1]],
                ['name' => $category[0], 'sort_order' => $position + 1],
            )->id,
        ]);

        if (Notice::query()->exists()) {
            return;
        }

        $notices = [
            ['admission', '2026-09-15', 'ভর্তি সংক্রান্ত বিজ্ঞপ্তি, বিস্তারিত জানতে ক্লিক করুন', true],
            ['exam', '2026-09-10', 'একাদশ শ্রেণির ২য় টেস্ট পরীক্ষার রুটিন বিজ্ঞপ্তি', true],
            ['exam', '2026-09-02', 'পরীক্ষার চূড়ান্ত তারিখের বিজ্ঞপ্তি', true],
            ['result', '2026-08-24', 'ভর্তি পরীক্ষার ফলাফল বিজ্ঞপ্তি', true],
            ['administrative', '2026-08-11', 'অভ্যন্তরীণ ক্রীড়া প্রতিযোগিতা সংক্রান্ত নোটিশ', false],
            ['academic', '2026-08-03', 'একাডেমিক ক্যালেন্ডার হালনাগাদ করা হয়েছে', false],
        ];

        foreach ($notices as [$slug, $date, $title, $inPopup]) {
            Notice::query()->create([
                'notice_category_id' => $categories[$slug],
                'title' => $title,
                'body' => "এতদ্বারা সংশ্লিষ্ট সকলের অবগতির জন্য জানানো যাচ্ছে যে, {$title}।\n\nবিস্তারিত তথ্যের জন্য কলেজ অফিসে যোগাযোগ করুন।",
                'published_on' => $date,
                'is_published' => true,
                'show_in_popup' => $inPopup,
            ]);
        }
    }
}
