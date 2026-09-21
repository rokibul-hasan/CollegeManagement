<?php

namespace App\Models;

use App\Enums\SiteTemplate;
use Database\Factories\TemplateSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Where a home section sits in one site template, and whether that template shows it.
 */
#[Fillable(['template', 'page_section_id', 'sort_order', 'is_active'])]
class TemplateSection extends Model
{
    /** @use HasFactory<TemplateSectionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'template' => SiteTemplate::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<PageSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(PageSection::class, 'page_section_id');
    }
}
