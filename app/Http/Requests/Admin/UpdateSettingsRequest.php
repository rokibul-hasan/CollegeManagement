<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('settings.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:150'],
            'site_name_en' => ['nullable', 'string', 'max:150'],
            'site_location' => ['nullable', 'string', 'max:150'],
            'eiin' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:300'],
            'show_top_bar' => ['boolean'],
            'ticker_text' => ['nullable', 'string', 'max:300'],
            'notice_popup_enabled' => ['boolean'],
            'footer_about' => ['nullable', 'string', 'max:600'],
            'footer_copyright' => ['nullable', 'string', 'max:200'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:300'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:300'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_logo' => ['boolean'],
        ];
    }
}
