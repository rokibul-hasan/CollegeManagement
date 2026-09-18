<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('menus.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menu = $this->route('menu');

        return [
            'location' => ['required', Rule::in(array_keys(Menu::LOCATIONS))],
            'parent_id' => [
                'nullable',
                Rule::exists('menus', 'id')->whereNull('parent_id')->where('location', $this->input('location')),
                Rule::notIn(array_filter([$menu?->id])),
            ],
            'label' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'string', 'max:500', 'regex:/^(\/|#|https?:\/\/|mailto:|tel:)/i'],
            'open_in_new_tab' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
            'url.regex' => 'লিংক অবশ্যই /, #, http://, https://, mailto: অথবা tel: দিয়ে শুরু হতে হবে।',
        ];
    }
}
