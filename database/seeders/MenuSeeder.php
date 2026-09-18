<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Seed the main menu, footer columns and quick links from the approved design.
     */
    public function run(): void
    {
        Menu::query()->delete();

        $this->seedLocation('main', [
            ['প্রচ্ছদ', '/'],
            ['প্রতিষ্ঠান সম্পর্কে', '/about'],
            ['শিক্ষক সম্পর্কে', '/teachers'],
            ['বিভাগসমূহ', '/departments'],
            ['নোটিশ', '/notices', [
                ['সকল নোটিশ', '/notices'],
                ['ভর্তি বিজ্ঞপ্তি', '/notices?category=admission'],
                ['পরীক্ষা ও রুটিন', '/notices?category=exam'],
                ['ফলাফল', '/notices?category=result'],
                ['প্রশাসনিক নোটিশ', '/notices?category=administrative'],
                ['টেন্ডার ও নিয়োগ', '/notices?category=tender'],
            ]],
            ['ক্লাস রুটিন', '/routine'],
            ['এডমিশন ফি', '/admission-fee'],
            ['ফরম পূরণ ফি', '/form-fee'],
            ['ফলাফল', '/result'],
            ['স্টুডেন্ট লগইন', '/student-login'],
            ['সহশিক্ষা কার্যক্রম', '/co-curricular'],
        ]);

        $this->seedLocation('footer', [
            ['প্রতিষ্ঠান', null, [
                ['প্রতিষ্ঠানের ইতিহাস', '/about'],
                ['একাডেমিক ক্যালেন্ডার', '/notices?category=academic'],
                ['শিক্ষকমন্ডলী', '/teachers'],
                ['যোগাযোগ', '/contact'],
            ]],
            ['শিক্ষার্থী', null, [
                ['নোটিশ', '/notices'],
                ['ক্লাস রুটিন', '/routine'],
                ['পরীক্ষার রুটিন', '/notices?category=exam'],
                ['ফলাফল', '/result'],
            ]],
            ['অফিসিয়াল লিংক', null, [
                ['শিক্ষা বোর্ড ফলাফল', 'http://www.educationboardresults.gov.bd/', [], true],
                ['ব্যানবেইস', 'https://banbeis.gov.bd/', [], true],
                ['ঢাকা শিক্ষা বোর্ড', 'https://dhakaeducationboard.gov.bd/', [], true],
                ['জাতীয় বিশ্ববিদ্যালয়', 'https://www.nu.ac.bd/', [], true],
            ]],
        ]);

        $this->seedLocation('quick', [
            ['কলেজ ম্যানেজমেন্ট সিস্টেম', '/admin'],
            ['অনলাইন ভর্তি আবেদন', '/admission-fee'],
            ['ক্লাস রুটিন', '/routine'],
            ['একাডেমিক ক্যালেন্ডার', '/notices?category=academic'],
            ['ফলাফল', '/result'],
            ['যোগাযোগ', '/contact'],
        ]);
    }

    /**
     * @param  array<int, array{0: string, 1: ?string, 2?: array<int, mixed>, 3?: bool}>  $items
     */
    private function seedLocation(string $location, array $items, ?int $parentId = null): void
    {
        foreach ($items as $position => $item) {
            $menu = Menu::query()->create([
                'location' => $location,
                'parent_id' => $parentId,
                'label' => $item[0],
                'url' => $item[1],
                'open_in_new_tab' => $item[3] ?? false,
                'sort_order' => $position + 1,
            ]);

            if (! empty($item[2])) {
                $this->seedLocation($location, $item[2], $menu->id);
            }
        }
    }
}
