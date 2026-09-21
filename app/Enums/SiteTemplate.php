<?php

namespace App\Enums;

/**
 * Public site designs the admin can switch between. Every template renders the same home
 * content, but keeps its own section order and visibility (see TemplateSection).
 */
enum SiteTemplate: string
{
    case Classic = 'classic';
    case Modern = 'modern';
    case Elegant = 'elegant';
    case Minimal = 'minimal';

    public function label(): string
    {
        return match ($this) {
            self::Classic => 'ক্লাসিক',
            self::Modern => 'মডার্ন',
            self::Elegant => 'এলিগ্যান্ট',
            self::Minimal => 'মিনিমাল',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Classic => 'নেভি ব্লু ও সোনালি রঙ, পাশে নোটিশ বোর্ডসহ স্লাইডার — প্রচলিত সরকারি কলেজের ধাঁচ।',
            self::Modern => 'সবুজাভ টিল রঙ, গোল কোণের কার্ড ও পূর্ণ প্রস্থের বড় হিরো ব্যানার।',
            self::Elegant => 'মেরুন ও ক্রিম রঙ, সেরিফ শিরোনাম ও ঐতিহ্যবাহী আবহ।',
            self::Minimal => 'সাদা-কালো পরিচ্ছন্ন ডিজাইন, কম সেকশন ও বেশি ফাঁকা জায়গা।',
        };
    }

    /**
     * Home section anchors in the order this template shows them when first set up or reset.
     * Sections whose anchor is missing here are appended after these, in their stored order.
     *
     * @return array<int, string>
     */
    public function presetOrder(): array
    {
        return match ($this) {
            self::Classic => ['hero', 'itihas', 'services', 'bibhag', 'bharti', 'shikkhok', 'routine', 'admission-fee', 'form-fee', 'result', 'login', 'cocurricular', 'gallery'],
            self::Modern => ['hero', 'services', 'bibhag', 'itihas', 'shikkhok', 'bharti', 'gallery', 'cocurricular', 'routine', 'admission-fee', 'form-fee', 'result', 'login'],
            self::Elegant => ['hero', 'itihas', 'shikkhok', 'bibhag', 'gallery', 'cocurricular', 'bharti', 'services', 'routine', 'admission-fee', 'form-fee', 'result', 'login'],
            self::Minimal => ['hero', 'services', 'itihas', 'bibhag', 'bharti', 'gallery', 'shikkhok', 'cocurricular', 'routine', 'admission-fee', 'form-fee', 'result', 'login'],
        };
    }

    /**
     * Anchors this template hides when first set up or reset. The admin can show them again.
     *
     * @return array<int, string>
     */
    public function presetHidden(): array
    {
        return match ($this) {
            self::Minimal => ['shikkhok', 'cocurricular', 'routine', 'admission-fee', 'form-fee', 'result', 'login'],
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $template) => [
            'value' => $template->value,
            'label' => $template->label(),
            'description' => $template->description(),
        ], self::cases());
    }
}
