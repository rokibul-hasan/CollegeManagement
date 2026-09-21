<?php

namespace App\Models;

use Database\Factories\PageSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['page_id', 'type', 'width', 'anchor', 'data', 'is_active', 'sort_order'])]
class PageSection extends Model
{
    /** @use HasFactory<PageSectionFactory> */
    use HasFactory;

    /**
     * Section types the page builder can render.
     *
     * @var array<int, string>
     */
    public const TYPES = ['hero', 'intro', 'text', 'cards', 'people', 'table', 'gallery', 'band', 'html', 'widget'];

    /**
     * Built-in dynamic blocks available through the "widget" section.
     *
     * @var array<int, string>
     */
    public const WIDGETS = ['result_form', 'student_login', 'contact_info', 'recent_notices'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Placement of this (home) section in each site template.
     *
     * @return HasMany<TemplateSection, $this>
     */
    public function templateSections(): HasMany
    {
        return $this->hasMany(TemplateSection::class);
    }
}
