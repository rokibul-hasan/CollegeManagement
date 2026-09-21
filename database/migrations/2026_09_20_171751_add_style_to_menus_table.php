<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The top bar links and header buttons that used to be hard-coded in the site header.
     *
     * @var array<string, array<int, array{label: string, url: string, style: ?string}>>
     */
    private const DEFAULTS = [
        'topbar' => [
            ['label' => 'অনলাইন ভর্তি', 'url' => '/admission-fee', 'style' => null],
            ['label' => 'লগইন', 'url' => '/student-login', 'style' => null],
        ],
        'header' => [
            ['label' => 'নোটিশ', 'url' => '/notices', 'style' => 'soft-live'],
            ['label' => 'ভর্তি তথ্য', 'url' => '/admission-fee', 'style' => 'primary'],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('style')->nullable()->after('url');
        });

        foreach (self::DEFAULTS as $location => $items) {
            if (Menu::query()->where('location', $location)->exists()) {
                continue;
            }

            foreach ($items as $position => $item) {
                Menu::query()->create($item + ['location' => $location, 'sort_order' => $position + 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('style');
        });
    }
};
