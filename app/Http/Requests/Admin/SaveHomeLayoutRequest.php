<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveHomeLayoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('pages.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = SavePageRequest::sectionRules();

        $rules['sections'] = ['present', 'array', 'max:60'];
        $rules['sections.*.id'] = [
            'nullable', 'integer', 'distinct',
            Rule::exists('page_sections', 'id')->where('page_id', Page::home()->getKey()),
        ];
        $rules['sections.*.anchor'][] = 'distinct';

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return SavePageRequest::sectionMessages() + [
            'sections.*.anchor.distinct' => 'একই অ্যাংকর দুটি সেকশনে দেওয়া যাবে না।',
        ];
    }

    /**
     * The submitted sections in display order. Section data is free-form (checked by the rules),
     * and validated() would strip its unlisted keys, so read it from the input instead.
     *
     * @return array<int, array{id: int|null, type: string, width: string, anchor: string|null, data: array<string, mixed>, is_active: bool}>
     */
    public function sections(): array
    {
        return collect($this->input('sections', []))
            ->map(fn (array $section) => [
                'id' => isset($section['id']) ? (int) $section['id'] : null,
                'type' => $section['type'],
                'width' => $section['width'],
                'anchor' => ($section['anchor'] ?? '') !== '' ? $section['anchor'] : null,
                'data' => $section['data'] ?? [],
                'is_active' => filter_var($section['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ])
            ->values()
            ->all();
    }
}
