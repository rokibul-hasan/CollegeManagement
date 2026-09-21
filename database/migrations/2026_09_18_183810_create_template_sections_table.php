<?php

use App\Enums\SiteTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CAMPUS = 'https://www.bssnews.net/bangla/assets/news_photos/2024/01/26/image-123842-1706246196.jpg';

    private const BUILDING = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPUQDVfddw8QvKH7togTst3dWCtiAcTHW6NVBmRbLKthrL6ddDy75D285o&s=10';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->string('anchor', 60)->nullable()->after('width');
        });

        Schema::create('template_sections', function (Blueprint $table) {
            $table->id();
            $table->string('template', 30);
            $table->foreignId('page_section_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->unique(['template', 'page_section_id']);
            $table->index(['template', 'is_active', 'sort_order']);
        });

        $this->createHomePage();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_sections');
        DB::table('pages')->where('slug', 'home')->delete();

        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropColumn('anchor');
        });
    }

    /**
     * Recreate the hard-coded home page as editable sections and lay it out for every template.
     */
    private function createHomePage(): void
    {
        $pageId = DB::table('pages')->insertGetId([
            'title' => 'হোম',
            'slug' => 'home',
            'layout' => 'full',
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idsByAnchor = [];

        foreach ($this->homeSections() as $order => [$anchor, $type, $data, $width]) {
            $idsByAnchor[$anchor] = DB::table('page_sections')->insertGetId([
                'page_id' => $pageId,
                'type' => $type,
                'width' => $width,
                'anchor' => $anchor,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'sort_order' => $order + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $rows = [];

        foreach (SiteTemplate::cases() as $template) {
            foreach ($template->presetOrder() as $position => $anchor) {
                $rows[] = [
                    'template' => $template->value,
                    'page_section_id' => $idsByAnchor[$anchor],
                    'sort_order' => $position + 1,
                    'is_active' => ! in_array($anchor, $template->presetHidden(), true),
                ];
            }
        }

        DB::table('template_sections')->insert($rows);
    }

    /**
     * The home page content that used to live in resources/js/data/content.js.
     *
     * @return array<int, array{0: string, 1: string, 2: array<string, mixed>, 3: string}>
     */
    private function homeSections(): array
    {
        $feeRows = fn (array $rows) => array_map(fn (string $cells) => ['cells' => $cells, 'link_label' => '', 'link_url' => ''], $rows);

        return [
            ['hero', 'hero', [
                'badge' => 'সরকারি কলেজ · ঢাকা শিক্ষা বোর্ড',
                'heading' => '',
                'lead' => 'মেয়েদের জন্য সম্মানিত শিক্ষার এক ঐতিহ্যবাহী প্রতিষ্ঠান — উচ্চ মাধ্যমিক, স্নাতক ও স্নাতকোত্তর স্তরে গুণগত শিক্ষা।',
                'show_notices' => '1',
                'slides' => [
                    ['image' => self::CAMPUS, 'alt' => 'কলেজ ক্যাম্পাস'],
                    ['image' => self::BUILDING, 'alt' => 'কলেজ ভবন'],
                    ['image' => self::CAMPUS, 'alt' => 'শিক্ষার্থীরা'],
                ],
                'buttons' => [
                    ['label' => 'ভর্তি আবেদন', 'url' => '/admission-fee', 'style' => 'primary'],
                    ['label' => 'বিভাগসমূহ', 'url' => '/departments', 'style' => 'ghost'],
                ],
            ], 'full'],
            ['itihas', 'intro', [
                'heading' => 'প্রতিষ্ঠানের পরিচিতি',
                'body' => 'গোপালগঞ্জ জেলার নারী শিক্ষা বিস্তারের লক্ষ্যে প্রতিষ্ঠিত এই কলেজ উচ্চ মাধ্যমিক থেকে স্নাতকোত্তর পর্যন্ত শিক্ষা কার্যক্রম পরিচালনা করছে।',
                'image' => self::BUILDING,
                'link_label' => 'বিস্তারিত',
                'link_url' => '/about',
                'messages' => [
                    ['title' => 'অধ্যক্ষের বাণী', 'name' => '[ নাম ]', 'photo' => '', 'text' => 'নৈতিকতা ও জ্ঞানের সমন্বয়ে শিক্ষার্থী গড়ে তোলাই আমাদের অঙ্গীকার।', 'url' => '/about'],
                    ['title' => 'উপাধ্যক্ষের বাণী', 'name' => '[ নাম ]', 'photo' => '', 'text' => 'নিয়মানুবর্তিতা ও সহমর্মিতার চর্চাই প্রতিষ্ঠানের প্রকৃত পরিচয়।', 'url' => '/about'],
                ],
                'show_quick_links' => '1',
                'cta_heading' => 'ফলাফল',
                'cta_text' => 'রোল ও রেজিস্ট্রেশন নম্বর দিয়ে মার্কশিট দেখুন।',
                'cta_label' => 'ফলাফল দেখুন',
                'cta_url' => '/result',
            ], 'full'],
            ['services', 'cards', [
                'heading' => 'দ্রুত সেবা', 'lead' => '', 'columns' => '3', 'style' => 'numbered', 'background' => 'white',
                'more_label' => '', 'more_url' => '',
                'items' => [
                    ['title' => 'পরীক্ষার ফলাফল', 'text' => 'মার্কশিট ও বোর্ড ফলাফল', 'meta' => '', 'url' => '/result'],
                    ['title' => 'ক্লাস রুটিন', 'text' => 'একাদশ ও দ্বাদশ শ্রেণি', 'meta' => '', 'url' => '/routine'],
                    ['title' => 'ডাউনলোড', 'text' => 'ফরম, বিজ্ঞপ্তি ও সিলেবাস', 'meta' => '', 'url' => '/notices'],
                    ['title' => 'একাডেমিক ক্যালেন্ডার', 'text' => 'বার্ষিক কর্মপরিকল্পনা', 'meta' => '', 'url' => '/notices?category=academic'],
                    ['title' => 'ছুটির তালিকা', 'text' => 'সরকারি ও প্রাতিষ্ঠানিক', 'meta' => '', 'url' => '/notices?category=administrative'],
                    ['title' => 'যোগাযোগ', 'text' => 'অফিস ও প্রশাসন', 'meta' => '', 'url' => '/contact'],
                ],
            ], 'full'],
            ['bibhag', 'cards', [
                'heading' => 'শিক্ষা কার্যক্রম', 'lead' => 'উচ্চ মাধ্যমিক পর্যায়ে তিনটি শাখা', 'columns' => '3', 'style' => 'default',
                'more_label' => 'সকল বিভাগ', 'more_url' => '/departments',
                'items' => [
                    ['title' => 'বিজ্ঞান', 'text' => 'পদার্থ, রসায়ন, জীববিজ্ঞান ও উচ্চতর গণিতসহ বিজ্ঞান শাখার পূর্ণাঙ্গ পাঠ্যক্রম।', 'meta' => '[ আসন সংখ্যা যোগ করতে হবে ]', 'url' => ''],
                    ['title' => 'মানবিক', 'text' => 'পৌরনীতি, অর্থনীতি, সমাজবিজ্ঞান, ইতিহাস ও ইসলামের ইতিহাস।', 'meta' => '[ আসন সংখ্যা যোগ করতে হবে ]', 'url' => ''],
                    ['title' => 'ব্যবসায় শিক্ষা', 'text' => 'হিসাববিজ্ঞান, ব্যবস্থাপনা, ফিন্যান্স ও ব্যাংকিং বিষয়ে পাঠদান।', 'meta' => '[ আসন সংখ্যা যোগ করতে হবে ]', 'url' => ''],
                ],
            ], 'full'],
            ['bharti', 'band', [
                'heading' => 'ভর্তি তথ্য',
                'text' => 'একাদশ শ্রেণিতে ভর্তি সরকারি নীতিমালা অনুসারে অনলাইনে সম্পন্ন হয়। স্নাতক ও স্নাতকোত্তর ভর্তির বিজ্ঞপ্তি নোটিশ বোর্ডে প্রকাশ করা হয়।',
                'links' => [
                    ['label' => 'অনলাইন ভর্তি আবেদন', 'url' => '/notices?category=admission'],
                    ['label' => 'ভর্তি বিজ্ঞপ্তি ডাউনলোড', 'url' => '/notices?category=admission'],
                    ['label' => 'পরীক্ষার ফলাফল', 'url' => '/result'],
                ],
            ], 'full'],
            ['shikkhok', 'people', [
                'heading' => 'শিক্ষকমন্ডলী', 'lead' => '', 'style' => 'grid',
                'more_label' => 'সকল শিক্ষক ও কর্মচারী', 'more_url' => '/teachers',
                'items' => [
                    ['name' => '[ নাম ]', 'role' => 'অধ্যক্ষ', 'photo' => '', 'text' => ''],
                    ['name' => '[ নাম ]', 'role' => 'উপাধ্যক্ষ', 'photo' => '', 'text' => ''],
                    ['name' => '[ নাম ]', 'role' => 'সহযোগী অধ্যাপক, বাংলা', 'photo' => '', 'text' => ''],
                    ['name' => '[ নাম ]', 'role' => 'প্রভাষক, ইংরেজি', 'photo' => '', 'text' => ''],
                ],
            ], 'full'],
            ['routine', 'table', [
                'heading' => 'ক্লাস রুটিন', 'lead' => '', 'style' => 'grid', 'columns' => 'শ্রেণি | শিফট | রুটিন', 'note' => '',
                'rows' => array_map(fn (string $cells) => ['cells' => $cells, 'link_label' => 'ডাউনলোড (PDF)', 'link_url' => '/notices?category=exam'], [
                    'একাদশ শ্রেণি | সকাল', 'দ্বাদশ শ্রেণি | সকাল', 'স্নাতক (পাস) | দিন', 'স্নাতকোত্তর | দিন',
                ]),
            ], 'full'],
            ['admission-fee', 'table', [
                'heading' => 'এডমিশন ফি', 'lead' => 'ভর্তি বছর ২০২৬ · শাখাভিত্তিক', 'style' => 'list', 'columns' => '',
                'rows' => $feeRows(['বিজ্ঞান | [ টাকা ]', 'মানবিক | [ টাকা ]', 'ব্যবসায় শিক্ষা | [ টাকা ]']),
                'note' => '[ প্রকৃত ফি প্রতিষ্ঠান থেকে নিশ্চিত করতে হবে ]',
            ], 'half'],
            ['form-fee', 'table', [
                'heading' => 'ফরম পূরণ ফি', 'lead' => 'বোর্ড পরীক্ষার ফরম পূরণ', 'style' => 'list', 'columns' => '',
                'rows' => $feeRows(['এসএসসি / এইচএসসি | [ টাকা ]', 'স্নাতক | [ টাকা ]', 'বিলম্ব ফি | [ টাকা ]']),
                'note' => '', 'more_label' => 'বিজ্ঞপ্তি ডাউনলোড', 'more_url' => '/notices?category=exam',
            ], 'half'],
            ['result', 'widget', ['widget' => 'result_form'], 'half'],
            ['login', 'widget', ['widget' => 'student_login'], 'half'],
            ['cocurricular', 'cards', [
                'heading' => 'সহশিক্ষা কার্যক্রম', 'lead' => 'পাঠ্যক্রমের বাইরে শিক্ষার্থীদের অংশগ্রহণের ক্ষেত্র', 'columns' => '3', 'style' => 'default',
                'more_label' => '', 'more_url' => '',
                'items' => [
                    ['title' => 'বিএনসিসি ও রোভার স্কাউট', 'text' => 'শৃঙ্খলা ও নেতৃত্ব চর্চার কার্যক্রম।', 'meta' => '', 'url' => ''],
                    ['title' => 'বিজ্ঞান ক্লাব', 'text' => 'প্রজেক্ট প্রদর্শনী ও বিজ্ঞান মেলা।', 'meta' => '', 'url' => ''],
                    ['title' => 'সাংস্কৃতিক সংসদ', 'text' => 'সংগীত, আবৃত্তি ও নাট্য পরিবেশনা।', 'meta' => '', 'url' => ''],
                    ['title' => 'বার্ষিক ক্রীড়া', 'text' => 'অ্যাথলেটিকস ও অন্তঃশ্রেণি প্রতিযোগিতা।', 'meta' => '', 'url' => ''],
                    ['title' => 'বিতর্ক ক্লাব', 'text' => 'আন্তঃকলেজ বিতর্ক ও উপস্থিত বক্তৃতা।', 'meta' => '', 'url' => ''],
                    ['title' => 'সামাজিক কার্যক্রম', 'text' => 'বৃক্ষরোপণ, রক্তদান ও পরিচ্ছন্নতা অভিযান।', 'meta' => '', 'url' => ''],
                ],
            ], 'full'],
            ['gallery', 'gallery', [
                'heading' => 'ক্যাম্পাসের মুহূর্ত', 'background' => 'white', 'more_label' => '', 'more_url' => '',
                'items' => [
                    ['image' => self::CAMPUS, 'caption' => 'সাংস্কৃতিক অনুষ্ঠান'],
                    ['image' => self::BUILDING, 'caption' => 'বার্ষিক ক্রীড়া'],
                    ['image' => self::CAMPUS, 'caption' => 'কলেজ পিকনিক'],
                    ['image' => self::BUILDING, 'caption' => 'শ্রেণিকক্ষ'],
                ],
            ], 'full'],
        ];
    }
};
