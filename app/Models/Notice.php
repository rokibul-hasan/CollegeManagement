<?php

namespace App\Models;

use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['notice_category_id', 'title', 'body', 'attachment', 'published_on', 'is_published', 'show_in_popup', 'is_pinned'])]
#[Appends(['attachment_url'])]
class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_on' => 'date:Y-m-d',
            'is_published' => 'boolean',
            'show_in_popup' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<NoticeCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(NoticeCategory::class, 'notice_category_id');
    }

    /**
     * @param  Builder<Notice>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->whereDate('published_on', '<=', now());
    }

    /**
     * @param  Builder<Notice>  $query
     */
    public function scopeLatestFirst(Builder $query): void
    {
        $query->orderByDesc('is_pinned')->orderByDesc('published_on')->orderByDesc('id');
    }

    /**
     * Public URL of the uploaded attachment, if any.
     */
    protected function attachmentUrl(): Attribute
    {
        return Attribute::get(fn () => $this->attachment ? asset('uploads/'.$this->attachment) : null);
    }
}
