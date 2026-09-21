<?php

namespace App\Models;

use App\Enums\SiteTemplate;
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
        'ticker_label' => 'সর্বশেষ',
        'ticker_link_label' => 'দেখুন →',
        'ticker_link_url' => '/notices',
        'notice_popup_enabled' => '1',
        'notice_popup_kicker' => 'জরুরি বিজ্ঞপ্তি',
        'notice_popup_title' => 'সাম্প্রতিক নোটিশ',
        'notice_board_title' => 'নোটিশ বোর্ড',
        'notice_board_tag' => 'NOTICE',
        'notice_board_more_label' => 'সব নোটিশ দেখুন →',
        'quick_links_title' => 'গুরুত্বপূর্ণ লিংক',
        'recent_notices_title' => 'সাম্প্রতিক নোটিশ',
        'footer_about' => '',
        'footer_copyright' => '© ২০২৬ গোপালগঞ্জ সরকারি মহিলা কলেজ',
        'facebook_url' => '',
        'youtube_url' => '',
        'instagram_url' => '',
        'linkedin_url' => '',
        'site_template' => 'classic',
        'video_popup_enabled' => '0',
        'video_popup_url' => '',
        'video_popup_title' => 'অধ্যক্ষের বাণী',
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
     * The site template visitors see, falling back to Classic if the stored value is unknown.
     */
    public static function template(): SiteTemplate
    {
        return SiteTemplate::tryFrom((string) self::allValues()['site_template']) ?? SiteTemplate::Classic;
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
