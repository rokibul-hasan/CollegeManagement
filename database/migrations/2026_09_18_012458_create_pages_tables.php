<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const CAMPUS = 'https://www.bssnews.net/bangla/assets/news_photos/2024/01/26/image-123842-1706246196.jpg';

    private const BUILDING = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPUQDVfddw8QvKH7togTst3dWCtiAcTHW6NVBmRbLKthrL6ddDy75D285o&s=10';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('lead', 500)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->string('layout')->default('full');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('width')->default('full');
            $table->json('data');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('pages.manage', 'web');
        Role::findByName('admin', 'web')->givePermissionTo('pages.manage');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->defaultPages() as $position => $page) {
            $pageId = DB::table('pages')->insertGetId([
                'title' => $page['title'],
                'slug' => $page['slug'],
                'lead' => $page['lead'],
                'layout' => $page['layout'],
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($page['sections'] as $order => $section) {
                DB::table('page_sections')->insert([
                    'page_id' => $pageId,
                    'type' => $section[0],
                    'width' => $section[2] ?? 'full',
                    'data' => json_encode($section[1], JSON_UNESCAPED_UNICODE),
                    'is_active' => true,
                    'sort_order' => $order + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('pages');
        Permission::query()->where('name', 'pages.manage')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * The inner pages that used to be hard-coded, recreated as editable sections.
     *
     * @return array<int, array{title: string, slug: string, lead: string, layout: string, sections: array<int, array{0: string, 1: array<string, mixed>, 2?: string}>}>
     */
    private function defaultPages(): array
    {
        $admissionBand = ['band', [
            'heading' => 'ভর্তি তথ্য',
            'text' => 'একাদশ শ্রেণিতে ভর্তি সরকারি নীতিমালা অনুসারে অনলাইনে সম্পন্ন হয়। স্নাতক ও স্নাতকোত্তর ভর্তির বিজ্ঞপ্তি নোটিশ বোর্ডে প্রকাশ করা হয়।',
            'links' => [
                ['label' => 'অনলাইন ভর্তি আবেদন', 'url' => '/notices?category=admission'],
                ['label' => 'ভর্তি বিজ্ঞপ্তি ডাউনলোড', 'url' => '/notices?category=admission'],
                ['label' => 'পরীক্ষার ফলাফল', 'url' => '/result'],
            ],
        ]];

        $gallery = ['gallery', [
            'heading' => 'ক্যাম্পাসের মুহূর্ত',
            'items' => [
                ['image' => self::CAMPUS, 'caption' => 'সাংস্কৃতিক অনুষ্ঠান'],
                ['image' => self::BUILDING, 'caption' => 'বার্ষিক ক্রীড়া'],
                ['image' => self::CAMPUS, 'caption' => 'কলেজ পিকনিক'],
                ['image' => self::BUILDING, 'caption' => 'শ্রেণিকক্ষ'],
            ],
        ]];

        return [
            [
                'title' => 'প্রতিষ্ঠান সম্পর্কে', 'slug' => 'about', 'layout' => 'sidebar',
                'lead' => 'প্রতিষ্ঠানের ইতিহাস, লক্ষ্য ও প্রশাসন',
                'sections' => [
                    ['text', [
                        'heading' => 'প্রতিষ্ঠানের ইতিহাস',
                        'body' => "গোপালগঞ্জ জেলার নারী শিক্ষা বিস্তারের লক্ষ্যে প্রতিষ্ঠিত এই কলেজ উচ্চ মাধ্যমিক থেকে স্নাতকোত্তর পর্যন্ত শিক্ষা কার্যক্রম পরিচালনা করছে।\n\nপ্রতিষ্ঠালগ্ন থেকে কলেজটি এ অঞ্চলের মেয়েদের উচ্চশিক্ষার প্রধান কেন্দ্র হিসেবে ভূমিকা রেখে আসছে। অভিজ্ঞ শিক্ষকমণ্ডলী, সমৃদ্ধ গ্রন্থাগার ও বিজ্ঞানাগার এবং নিয়মিত সহশিক্ষা কার্যক্রমের মাধ্যমে শিক্ষার্থীদের সার্বিক বিকাশ নিশ্চিত করা হয়।",
                        'image' => self::BUILDING, 'image_position' => 'top', 'style' => 'card',
                    ]],
                    ['table', [
                        'heading' => 'এক নজরে', 'style' => 'list',
                        'rows' => [
                            ['cells' => 'প্রতিষ্ঠাকাল | [ সাল ]'],
                            ['cells' => 'ইআইআইএন | ১০৯৪৮৩'],
                            ['cells' => 'শিক্ষা বোর্ড | ঢাকা'],
                            ['cells' => 'অধিভুক্তি | জাতীয় বিশ্ববিদ্যালয়'],
                        ],
                    ]],
                    ['text', ['heading' => 'লক্ষ্য', 'body' => 'নৈতিকতা, জ্ঞান ও দক্ষতার সমন্বয়ে আত্মবিশ্বাসী, দায়িত্বশীল ও আলোকিত নারী সমাজ গড়ে তোলা।', 'style' => 'card'], 'half'],
                    ['text', ['heading' => 'উদ্দেশ্য', 'body' => 'দেশের অন্যতম শ্রেষ্ঠ নারী শিক্ষা প্রতিষ্ঠান হিসেবে গুণগত ও আধুনিক শিক্ষা নিশ্চিত করা।', 'style' => 'card'], 'half'],
                    ['people', [
                        'heading' => 'বাণী', 'style' => 'message',
                        'items' => [
                            ['name' => '[ নাম ]', 'role' => 'অধ্যক্ষ', 'photo' => '', 'text' => 'নৈতিকতা ও জ্ঞানের সমন্বয়ে শিক্ষার্থী গড়ে তোলাই আমাদের অঙ্গীকার।'],
                            ['name' => '[ নাম ]', 'role' => 'উপাধ্যক্ষ', 'photo' => '', 'text' => 'নিয়মানুবর্তিতা ও সহমর্মিতার চর্চাই প্রতিষ্ঠানের প্রকৃত পরিচয়।'],
                        ],
                    ]],
                    $gallery,
                ],
            ],
            [
                'title' => 'শিক্ষক সম্পর্কে', 'slug' => 'teachers', 'layout' => 'full',
                'lead' => 'অভিজ্ঞ ও নিবেদিতপ্রাণ শিক্ষকমণ্ডলী এবং কর্মকর্তা-কর্মচারীবৃন্দ।',
                'sections' => [
                    ['people', [
                        'heading' => 'শিক্ষকমন্ডলী', 'style' => 'grid',
                        'items' => [
                            ['name' => '[ নাম ]', 'role' => 'অধ্যক্ষ', 'photo' => '', 'text' => ''],
                            ['name' => '[ নাম ]', 'role' => 'উপাধ্যক্ষ', 'photo' => '', 'text' => ''],
                            ['name' => '[ নাম ]', 'role' => 'সহযোগী অধ্যাপক, বাংলা', 'photo' => '', 'text' => ''],
                            ['name' => '[ নাম ]', 'role' => 'প্রভাষক, ইংরেজি', 'photo' => '', 'text' => ''],
                            ['name' => '[ নাম ]', 'role' => 'সহকারী অধ্যাপক, হিসাববিজ্ঞান', 'photo' => '', 'text' => ''],
                            ['name' => '[ নাম ]', 'role' => 'প্রভাষক, রসায়ন', 'photo' => '', 'text' => ''],
                        ],
                    ]],
                ],
            ],
            [
                'title' => 'বিভাগসমূহ', 'slug' => 'departments', 'layout' => 'full',
                'lead' => 'উচ্চ মাধ্যমিক শাখা এবং স্নাতক ও স্নাতকোত্তর পর্যায়ের বিভাগসমূহ।',
                'sections' => [
                    ['cards', [
                        'heading' => 'উচ্চ মাধ্যমিক শাখা', 'lead' => 'উচ্চ মাধ্যমিক পর্যায়ে তিনটি শাখা', 'columns' => '3',
                        'items' => [
                            ['title' => 'বিজ্ঞান', 'text' => 'পদার্থ, রসায়ন, জীববিজ্ঞান ও উচ্চতর গণিতসহ বিজ্ঞান শাখার পূর্ণাঙ্গ পাঠ্যক্রম।', 'meta' => '[ আসন সংখ্যা যোগ করতে হবে ]', 'url' => ''],
                            ['title' => 'মানবিক', 'text' => 'পৌরনীতি, অর্থনীতি, সমাজবিজ্ঞান, ইতিহাস ও ইসলামের ইতিহাস।', 'meta' => '[ আসন সংখ্যা যোগ করতে হবে ]', 'url' => ''],
                            ['title' => 'ব্যবসায় শিক্ষা', 'text' => 'হিসাববিজ্ঞান, ব্যবস্থাপনা, ফিন্যান্স ও ব্যাংকিং বিষয়ে পাঠদান।', 'meta' => '[ আসন সংখ্যা যোগ করতে হবে ]', 'url' => ''],
                        ],
                    ]],
                    ['cards', [
                        'heading' => 'স্নাতক ও স্নাতকোত্তর বিভাগ', 'lead' => 'জাতীয় বিশ্ববিদ্যালয় অধিভুক্ত', 'columns' => '4',
                        'items' => array_map(fn (string $name) => ['title' => $name, 'text' => '', 'meta' => '', 'url' => ''], [
                            'বাংলা', 'ইংরেজি', 'ইতিহাস', 'ইসলামের ইতিহাস ও সংস্কৃতি', 'রাষ্ট্রবিজ্ঞান', 'অর্থনীতি',
                            'সমাজবিজ্ঞান', 'দর্শন', 'হিসাববিজ্ঞান', 'ব্যবস্থাপনা', 'পদার্থবিজ্ঞান', 'রসায়ন',
                        ]),
                    ]],
                    $admissionBand,
                ],
            ],
            [
                'title' => 'ক্লাস রুটিন', 'slug' => 'routine', 'layout' => 'sidebar',
                'lead' => 'শ্রেণি ও শিফটভিত্তিক ক্লাস রুটিন এবং পরীক্ষার সময়সূচি।',
                'sections' => [
                    ['table', [
                        'heading' => '', 'style' => 'grid', 'columns' => 'শ্রেণি | শিফট | রুটিন',
                        'rows' => [
                            ['cells' => 'একাদশ শ্রেণি | সকাল', 'link_label' => 'ডাউনলোড (PDF)', 'link_url' => '/notices?category=exam'],
                            ['cells' => 'দ্বাদশ শ্রেণি | সকাল', 'link_label' => 'ডাউনলোড (PDF)', 'link_url' => '/notices?category=exam'],
                            ['cells' => 'স্নাতক (পাস) | দিন', 'link_label' => 'ডাউনলোড (PDF)', 'link_url' => '/notices?category=exam'],
                            ['cells' => 'স্নাতকোত্তর | দিন', 'link_label' => 'ডাউনলোড (PDF)', 'link_url' => '/notices?category=exam'],
                        ],
                    ]],
                    ['text', [
                        'heading' => 'পরীক্ষার রুটিন', 'style' => 'card',
                        'body' => 'টেস্ট, নির্বাচনী ও বোর্ড পরীক্ষার রুটিন নোটিশ বোর্ডের "পরীক্ষা ও রুটিন" বিভাগে প্রকাশ করা হয়।',
                        'button_label' => 'পরীক্ষার রুটিন দেখুন', 'button_url' => '/notices?category=exam',
                    ]],
                ],
            ],
            [
                'title' => 'এডমিশন ফি', 'slug' => 'admission-fee', 'layout' => 'sidebar',
                'lead' => 'শাখাভিত্তিক ভর্তি ফি ও ভর্তি সংক্রান্ত তথ্য।',
                'sections' => [
                    ['table', [
                        'heading' => 'এডমিশন ফি', 'lead' => 'ভর্তি বছর ২০২৬ · শাখাভিত্তিক', 'style' => 'list',
                        'rows' => [['cells' => 'বিজ্ঞান | [ টাকা ]'], ['cells' => 'মানবিক | [ টাকা ]'], ['cells' => 'ব্যবসায় শিক্ষা | [ টাকা ]']],
                        'note' => '[ প্রকৃত ফি প্রতিষ্ঠান থেকে নিশ্চিত করতে হবে ]',
                    ]],
                    ['text', [
                        'heading' => 'ভর্তির প্রয়োজনীয় কাগজপত্র', 'style' => 'card',
                        'body' => "- এসএসসি / এইচএসসি পরীক্ষার মূল নম্বরপত্র ও প্রশংসাপত্র\n- সদ্য তোলা পাসপোর্ট সাইজের ছবি\n- জন্ম নিবন্ধন সনদের সত্যায়িত কপি\n- অনলাইন আবেদনের কপি ও নিশ্চয়ন স্লিপ",
                    ]],
                    $admissionBand,
                ],
            ],
            [
                'title' => 'ফরম পূরণ ফি', 'slug' => 'form-fee', 'layout' => 'sidebar',
                'lead' => 'বোর্ড ও বিশ্ববিদ্যালয় পরীক্ষার ফরম পূরণের ফি ও সময়সূচি।',
                'sections' => [
                    ['table', [
                        'heading' => 'ফরম পূরণ ফি', 'lead' => 'বোর্ড পরীক্ষার ফরম পূরণ', 'style' => 'list',
                        'rows' => [['cells' => 'এসএসসি / এইচএসসি | [ টাকা ]'], ['cells' => 'স্নাতক | [ টাকা ]'], ['cells' => 'বিলম্ব ফি | [ টাকা ]']],
                        'note' => '[ প্রকৃত ফি প্রতিষ্ঠান থেকে নিশ্চিত করতে হবে ]',
                    ]],
                    ['text', [
                        'heading' => 'জরুরি নির্দেশনা', 'style' => 'card',
                        'body' => 'নির্ধারিত সময়ের পর ফরম পূরণ করলে বিলম্ব ফি প্রযোজ্য হবে। সময়সূচি ও বিস্তারিত নির্দেশনা নোটিশ বোর্ডে প্রকাশ করা হয়।',
                        'button_label' => 'বিজ্ঞপ্তি দেখুন', 'button_url' => '/notices?category=exam',
                    ]],
                ],
            ],
            [
                'title' => 'ফলাফল', 'slug' => 'result', 'layout' => 'full',
                'lead' => 'প্রতিষ্ঠানের অভ্যন্তরীণ ও বোর্ড পরীক্ষার ফলাফল।',
                'sections' => [
                    ['widget', ['widget' => 'result_form'], 'half'],
                    ['cards', [
                        'heading' => 'বোর্ড ও বিশ্ববিদ্যালয় ফলাফল', 'columns' => '1',
                        'items' => [
                            ['title' => 'শিক্ষা বোর্ড ফলাফল', 'text' => 'এসএসসি ও এইচএসসি', 'meta' => '', 'url' => 'http://www.educationboardresults.gov.bd/'],
                            ['title' => 'জাতীয় বিশ্ববিদ্যালয় ফলাফল', 'text' => 'স্নাতক ও স্নাতকোত্তর', 'meta' => '', 'url' => 'https://www.nu.ac.bd/results/'],
                            ['title' => 'প্রকাশিত ফলাফল বিজ্ঞপ্তি', 'text' => 'কলেজের নোটিশ বোর্ড', 'meta' => '', 'url' => '/notices?category=result'],
                        ],
                    ], 'half'],
                ],
            ],
            [
                'title' => 'স্টুডেন্ট লগইন', 'slug' => 'student-login', 'layout' => 'full',
                'lead' => 'উপস্থিতি, ফি ও ফলাফল দেখতে নিজের আইডি দিয়ে প্রবেশ করুন।',
                'sections' => [
                    ['widget', ['widget' => 'student_login'], 'half'],
                    ['text', [
                        'heading' => 'স্টুডেন্ট পোর্টালে যা থাকবে', 'style' => 'dark',
                        'body' => "- দৈনিক উপস্থিতি\n- ফি পরিশোধের হিসাব\n- পরীক্ষার ফলাফল ও মার্কশিট\n- ক্লাস রুটিন ও ব্যক্তিগত নোটিশ",
                    ], 'half'],
                ],
            ],
            [
                'title' => 'সহশিক্ষা কার্যক্রম', 'slug' => 'co-curricular', 'layout' => 'full',
                'lead' => 'পাঠ্যক্রমের বাইরে শিক্ষার্থীদের অংশগ্রহণের ক্ষেত্র।',
                'sections' => [
                    ['cards', [
                        'heading' => '', 'columns' => '3',
                        'items' => [
                            ['title' => 'বিএনসিসি ও রোভার স্কাউট', 'text' => 'শৃঙ্খলা ও নেতৃত্ব চর্চার কার্যক্রম।', 'meta' => '', 'url' => ''],
                            ['title' => 'বিজ্ঞান ক্লাব', 'text' => 'প্রজেক্ট প্রদর্শনী ও বিজ্ঞান মেলা।', 'meta' => '', 'url' => ''],
                            ['title' => 'সাংস্কৃতিক সংসদ', 'text' => 'সংগীত, আবৃত্তি ও নাট্য পরিবেশনা।', 'meta' => '', 'url' => ''],
                            ['title' => 'বার্ষিক ক্রীড়া', 'text' => 'অ্যাথলেটিকস ও অন্তঃশ্রেণি প্রতিযোগিতা।', 'meta' => '', 'url' => ''],
                            ['title' => 'বিতর্ক ক্লাব', 'text' => 'আন্তঃকলেজ বিতর্ক ও উপস্থিত বক্তৃতা।', 'meta' => '', 'url' => ''],
                            ['title' => 'সামাজিক কার্যক্রম', 'text' => 'বৃক্ষরোপণ, রক্তদান ও পরিচ্ছন্নতা অভিযান।', 'meta' => '', 'url' => ''],
                        ],
                    ]],
                    $gallery,
                ],
            ],
            [
                'title' => 'যোগাযোগ', 'slug' => 'contact', 'layout' => 'sidebar',
                'lead' => 'অফিস ও প্রশাসনের সাথে যোগাযোগের তথ্য।',
                'sections' => [
                    ['widget', ['widget' => 'contact_info']],
                ],
            ],
        ];
    }
};
