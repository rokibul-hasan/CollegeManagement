<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use App\Models\PageSection;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePageRequest extends FormRequest
{
    private const SAFE_URL = '/^(\/|#|https?:\/\/|mailto:|tel:)/i';

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
        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'required', 'string', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::notIn(Page::RESERVED_SLUGS),
                Rule::unique('pages', 'slug')->ignore($this->route('page')),
            ],
            'lead' => ['nullable', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'layout' => ['required', Rule::in(['full', 'sidebar'])],
            'is_published' => ['boolean'],
            'sections' => ['array', 'max:60'],
            'sections.*.type' => ['required', Rule::in(PageSection::TYPES)],
            'sections.*.width' => ['required', Rule::in(['full', 'half'])],
            'sections.*.is_active' => ['boolean'],
            'sections.*.data' => ['present', 'array', $this->safeSectionData()],
            'sections.*.data.widget' => ['nullable', Rule::in(PageSection::WIDGETS)],
            'sections.*.data.html' => ['nullable', 'string', 'max:100000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'স্লাগে শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন ব্যবহার করুন।',
            'slug.not_in' => 'এই স্লাগটি সিস্টেমের জন্য সংরক্ষিত, অন্য নাম দিন।',
        ];
    }

    /**
     * Section data is free-form, so check its size and every link inside it.
     */
    private function safeSectionData(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (strlen((string) json_encode($value)) > 200000) {
                $fail('একটি সেকশনের কনটেন্ট খুব বড়।');

                return;
            }

            array_walk_recursive($value, function (mixed $item, string|int $key) use ($fail): void {
                $isLinkField = in_array($key, ['url', 'link_url', 'button_url'], true);

                if ($isLinkField && is_string($item) && $item !== '' && ! preg_match(self::SAFE_URL, $item)) {
                    $fail('লিংক অবশ্যই /, #, http://, https://, mailto: অথবা tel: দিয়ে শুরু হতে হবে।');
                }
            });
        };
    }
}
