<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['location', 'parent_id', 'label', 'url', 'style', 'open_in_new_tab', 'is_active', 'sort_order'])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    /**
     * Where a menu can be rendered on the public site.
     *
     * @var array<string, string>
     */
    public const LOCATIONS = [
        'main' => 'প্রধান মেনু',
        'topbar' => 'টপ বার',
        'header' => 'হেডারের বাটন',
        'footer' => 'ফুটার',
        'quick' => 'গুরুত্বপূর্ণ লিংক',
    ];

    /**
     * Button looks available to header menu items. The "-live" variant adds the blinking dot.
     *
     * @var array<string, string>
     */
    public const STYLES = [
        'soft' => 'হালকা বাটন',
        'soft-live' => 'হালকা বাটন + জ্বলজ্বলে ডট',
        'primary' => 'রঙিন বাটন',
        'gold' => 'সোনালি বাটন',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * @return HasMany<Menu, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Menu>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
