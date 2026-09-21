<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

/**
 * Fills the "শিক্ষক সম্পর্কে" page from the college's own teacher and staff register
 * (Teacher+ Karmocari.doc), whose portraits are published under /uploads/staff.
 *
 * The register also carries each person's mobile number and blood group. Those are
 * deliberately left out here — add them in the page editor if the college decides
 * they should be public.
 */
class StaffSeeder extends Seeder
{
    /**
     * Portraits are numbered in the order the register embeds them.
     */
    private const PHOTO = '/uploads/staff/photo-%02d.jpg';

    /**
     * @var array{0: string, 1: string, 2: int}
     */
    private const PRINCIPAL = ['Prof. Sheikh Benozir Ahemed', 'অধ্যক্ষ', 1];

    /**
     * Teaching staff by department, in register order.
     *
     * @var array<string, array<int, array{0: string, 1: string, 2: int}>>
     */
    private const DEPARTMENTS = [
        'বাংলা বিভাগ' => [
            ['Khondoker Sabina Yesmin', 'সহযোগী অধ্যাপক', 2],
            ['Nihar Ranjan Kanzilal', 'সহকারী অধ্যাপক', 3],
            ['Rifat Ara Rimu', 'প্রভাষক', 4],
            ['Shahin Hossain', 'প্রভাষক', 5],
        ],
        'ইংরেজি বিভাগ' => [
            ['Bipul Kumar Biswas', 'সহকারী অধ্যাপক', 24],
            ['Mohua Rani Dey', 'সহকারী অধ্যাপক', 25],
            ['Rupa Khanam', 'সহকারী অধ্যাপক', 26],
        ],
        'অর্থনীতি বিভাগ' => [
            ['Mohammad Nur Hossain', 'সহকারী অধ্যাপক', 18],
            ['Md. Khalid Hossain Sarder', 'সহকারী অধ্যাপক', 19],
            ['Mahanambrata Sarker', 'সহকারী অধ্যাপক', 20],
            ['Md. Aiub Hossain', 'প্রভাষক', 21],
        ],
        'রাষ্ট্রবিজ্ঞান বিভাগ' => [
            ['Dr. Shimul Barai', 'সহকারী অধ্যাপক', 6],
            ['Kazi Borhan Uddin', 'সহকারী অধ্যাপক', 7],
            ['Mursida', 'প্রভাষক', 8],
        ],
        'সমাজবিজ্ঞান বিভাগ' => [
            ['Sima Sarker', 'সহকারী অধ্যাপক', 9],
            ['Md. Razu Ahammed', 'প্রভাষক', 10],
            ['Md. Nazmul Hasan', 'প্রভাষক', 11],
        ],
        'দর্শন বিভাগ' => [
            ['Emamul Islam', 'সহকারী অধ্যাপক', 22],
            ['Mihir Baowali', 'প্রভাষক', 23],
        ],
        'ইসলামী শিক্ষা বিভাগ' => [
            ['Professor A.K.M. Shahabuddin', 'অধ্যাপক', 12],
            ['Dr. Mohammad Anisur Rahman', 'সহযোগী অধ্যাপক', 13],
            ['Mohammad Abu Amer', 'সহযোগী অধ্যাপক', 14],
            ['Kamal Uddin', 'প্রভাষক', 15],
        ],
        'ইসলামের ইতিহাস ও সংস্কৃতি বিভাগ' => [
            ['Zesmin Akter', 'সহকারী অধ্যাপক', 30],
            ['Mir Rajoan Mahmud', 'প্রভাষক', 31],
        ],
        'ইতিহাস বিভাগ' => [
            ['Mabia Khatun', 'প্রভাষক', 32],
            ['Md. Shamim Hosen', 'প্রভাষক', 33],
        ],
        'সংস্কৃত বিভাগ' => [
            ['Kabita Rani Sarker', 'সহকারী অধ্যাপক', 28],
            ['Suzan Mandal', 'প্রভাষক', 29],
        ],
        'গার্হস্থ্য অর্থনীতি বিভাগ' => [
            ['Shammi Akhtar', 'সহকারী অধ্যাপক', 16],
            ['Nazma Khanam', 'সহকারী অধ্যাপক', 17],
        ],
        'পদার্থবিজ্ঞান বিভাগ' => [
            ['Bidhan Biswas', 'সহকারী অধ্যাপক', 34],
        ],
        'রসায়ন বিভাগ' => [
            ['Mohammad Al-Amin', 'প্রভাষক', 36],
        ],
        'গণিত বিভাগ' => [
            ['Md. Rashedul Islam', 'প্রভাষক', 35],
        ],
        'প্রাণিবিদ্যা বিভাগ' => [
            ['Md. Asaduzzaman Khan', 'সহকারী অধ্যাপক', 27],
        ],
    ];

    /**
     * @var array<int, array{0: string, 1: string, 2: int}>
     */
    private const OFFICE_STAFF = [
        ['Badiruzzaman', 'অফিস সহকারী কাম কম্পিউটার অপারেটর', 37],
        ['Sajib Mondal', 'কম্পিউটার অপারেটর', 46],
        ['Marufur Rahman Daria', 'কম্পিউটার ল্যাবরেটরি সহকারী', 39],
        ['Tama Khanom', 'স্টোর কিপার', 38],
        ['Md Bakaddes Molla', 'অফিস সহকারী', 40],
        ['Niva Rani Sarkar', 'অফিস সহকারী', 41],
        ['Rupa Sheikh', 'অফিস সহকারী', 42],
        ['Sima Akter', 'অফিস সহকারী', 44],
        ['Sarmin Khanom', 'অফিস সহকারী', 45],
        ['শিল্পী খানম', 'অফিস সহকারী', 49],
        ['লিন্ডা হীরা', 'অফিস সহকারী', 50],
        ['মৃণাল বৈদ্য', 'অফিস সহায়ক কাম ইলেকট্রিশিয়ান', 54],
        ['মো: ফয়সাল মোল্যা', 'অফিস সহায়ক', 56],
        ['জাহিদুল ইসলাম ফকির', 'অফিস সহায়ক', 57],
        ['রাহাতুল ইসলাম জয়', 'অফিস সহায়ক', 59],
        ['মিলন কুমার মণ্ডল', 'অফিস সহায়ক', 60],
        ['মোঃ বাইজিদ মোল্লা', 'অফিস সহায়ক', 62],
        ['সজল বিশ্বাস', 'দক্ষ শ্রমিক', 47],
        ['অংশুমান কীর্তনীয়া', 'কলেজ গার্ড', 55],
        ['গোলাম হোসেন মোল্লা', 'প্রহরী', 51],
        ['কাজী কামরুল ইসলাম', 'নৈশ প্রহরী', 53],
        ['রাশিদা বেগম', 'আয়া', 52],
        ['গীতা বিশ্বাস', 'পাঁচক', 58],
        ['শাহিদুল মোল্লা', 'মালী', 61],
        ['মানিক জমাদার', 'পরিচ্ছন্নতা কর্মী', 48],
        ['যমুনা বিশ্বাস', 'পরিচ্ছন্নতা কর্মী', 63],
        ['Mosammot Sultana Yeasmin', 'পরিচ্ছন্নতা কর্মী', 43],
    ];

    /**
     * @var array<int, array{0: string, 1: string, 2: int}>
     */
    private const HOSTEL_STAFF = [
        ['জ্যোৎস্না বৈদ্য', 'সহকারী মেট্রন', 64],
        ['পান্নু শেখ', 'নৈশ প্রহরী', 66],
        ['রীতা জমাদ্দার', 'পরিচ্ছন্নতা কর্মী', 65],
        ['চিত্রা বিশ্বাস', 'পরিচ্ছন্নতা কর্মী', 67],
        ['তাসলিমা', 'রাধুনী', 0],
        ['শ্যামলা বেগম', 'রাধুনী', 0],
    ];

    /**
     * Rebuild the teachers page from the register.
     */
    public function run(): void
    {
        $page = Page::query()->where('slug', 'teachers')->first();

        if (! $page) {
            $this->command?->warn('teachers পাতা পাওয়া যায়নি — আগে মাইগ্রেশন চালান।');

            return;
        }

        $page->update(['lead' => 'কলেজের অধ্যক্ষ, বিভাগভিত্তিক শিক্ষকমণ্ডলী এবং কর্মকর্তা-কর্মচারীবৃন্দ।']);
        $page->sections()->delete();

        foreach ($this->sections() as $order => [$type, $data]) {
            $page->sections()->create([
                'type' => $type,
                'width' => 'full',
                'data' => $data,
                'is_active' => true,
                'sort_order' => $order + 1,
            ]);
        }

        $this->nameThePrincipalOnHome();

        $this->command?->info(sprintf(
            '%d জন শিক্ষক ও %d জন কর্মচারী যোগ করা হয়েছে।',
            1 + array_sum(array_map('count', self::DEPARTMENTS)),
            count(self::OFFICE_STAFF) + count(self::HOSTEL_STAFF),
        ));
    }

    /**
     * The home page ships with "[ নাম ]" under অধ্যক্ষের বাণী; fill it from the register.
     */
    private function nameThePrincipalOnHome(): void
    {
        [$name, $role, $photo] = self::PRINCIPAL;

        foreach (PageSection::query()->where('type', 'intro')->get() as $section) {
            $data = $section->data;

            $data['messages'] = array_map(function (array $message) use ($name, $role, $photo): array {
                if (str_contains($message['title'] ?? '', $role)) {
                    $message['name'] = $name;
                    $message['photo'] = sprintf(self::PHOTO, $photo);
                }

                return $message;
            }, $data['messages'] ?? []);

            $section->update(['data' => $data]);
        }

        Page::forgetHomeCache();
    }

    /**
     * @return array<int, array{0: string, 1: array<string, mixed>}>
     */
    private function sections(): array
    {
        $sections = [['people', $this->people('অধ্যক্ষ', [self::PRINCIPAL])]];

        foreach (self::DEPARTMENTS as $department => $people) {
            $sections[] = ['people', $this->people($department, $people)];
        }

        $sections[] = ['people', $this->people('কর্মকর্তা ও কর্মচারীবৃন্দ', self::OFFICE_STAFF)];
        $sections[] = ['people', $this->people('হোস্টেল কর্মচারীবৃন্দ', self::HOSTEL_STAFF)];

        return $sections;
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: int}>  $people
     * @return array<string, mixed>
     */
    private function people(string $heading, array $people): array
    {
        return [
            'heading' => $heading,
            'lead' => '',
            'style' => 'grid',
            'more_label' => '',
            'more_url' => '',
            'items' => array_map(fn (array $person) => [
                'name' => $person[0],
                'role' => $person[1],
                'photo' => $person[2] ? sprintf(self::PHOTO, $person[2]) : '',
                'text' => '',
            ], $people),
        ];
    }
}
