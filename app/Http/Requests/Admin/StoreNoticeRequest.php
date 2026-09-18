<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('notices.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'notice_category_id' => ['nullable', 'exists:notice_categories,id'],
            'body' => ['nullable', 'string', 'max:20000'],
            'published_on' => ['required', 'date'],
            'is_published' => ['boolean'],
            'show_in_popup' => ['boolean'],
            'is_pinned' => ['boolean'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
            'remove_attachment' => ['boolean'],
        ];
    }
}
