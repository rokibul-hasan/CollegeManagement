<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * Default values for every editable site setting.
     *
     * @var array<string, string|null>
     */
    public const DEFAULTS = [
        'site_name' => 'গোপালগঞ্জ সরকারি মহিলা কলেজ',
        'site_name_en' => 'GOPALGANJ GOVT. MOHILA COLLEGE',
        'site_location' => 'গোপালগঞ্জ, ঢাকা',
        'logo' => null,
        'eiin' => '১০৯৪৮৩',
        'phone' => '+৮৮০১৩০৯১০৯৪৮৩',
        'email' => 'skfmc.gov.109483@gmail.com',
        'address' => 'গোপালগঞ্জ সদর, গোপালগঞ্জ, ঢাকা, বাংলাদেশ',
        'show_top_bar' => '1',
        'ticker_text' => 'ভর্তি সংক্রান্ত বিজ্ঞপ্তি প্রকাশিত হয়েছে — বিস্তারিত জানতে নোটিশ বোর্ড দেখুন।',
        'notice_popup_enabled' => '1',
        'footer_about' => '',
        'footer_copyright' => '© ২০২৬ গোপালগঞ্জ সরকারি মহিলা কলেজ',
        'facebook_url' => '',
        'youtube_url' => '',
    ];

    private const CACHE_KEY = 'site_settings';

    /**
     * All settings merged over their defaults.
     *
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());

        return array_merge(self::DEFAULTS, array_intersect_key($stored, self::DEFAULTS));
    }

    /**
     * Persist the given key/value pairs, ignoring unknown keys.
     *
     * @param  array<string, string|null>  $values
     */
    public static function store(array $values): void
    {
        foreach (array_intersect_key($values, self::DEFAULTS) as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
