<?php

namespace App\Http\Requests\Admin;

use App\Enums\SiteTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * A YouTube watch, short, live, embed or youtu.be link with an 11-character video id.
     */
    private const YOUTUBE_URL = '/^https:\/\/(www\.|m\.)?(youtube\.com\/(watch\?(.*&)?v=|shorts\/|live\/|embed\/)|youtu\.be\/)[A-Za-z0-9_-]{11}/';

    /**
     * A link an editor may point a label at: an in-site path, an anchor, or an external/contact scheme.
     */
    private const SAFE_URL = '/^(\/|#|https?:\/\/|mailto:|tel:)/i';

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
            'ticker_label' => ['nullable', 'string', 'max:40'],
            'ticker_link_label' => ['nullable', 'string', 'max:40'],
            'ticker_link_url' => ['nullable', 'string', 'max:300', 'regex:'.self::SAFE_URL],
            'notice_popup_enabled' => ['boolean'],
            'notice_popup_kicker' => ['nullable', 'string', 'max:60'],
            'notice_popup_title' => ['nullable', 'string', 'max:120'],
            'notice_board_title' => ['nullable', 'string', 'max:60'],
            'notice_board_tag' => ['nullable', 'string', 'max:30'],
            'notice_board_more_label' => ['nullable', 'string', 'max:60'],
            'quick_links_title' => ['nullable', 'string', 'max:60'],
            'recent_notices_title' => ['nullable', 'string', 'max:60'],
            'footer_about' => ['nullable', 'string', 'max:600'],
            'footer_copyright' => ['nullable', 'string', 'max:200'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:300'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:300'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:300'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:300'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_logo' => ['boolean'],
            'site_template' => ['sometimes', Rule::enum(SiteTemplate::class)],
            'video_popup_enabled' => ['boolean'],
            'video_popup_url' => ['nullable', 'required_if_accepted:video_popup_enabled', 'url:https', 'max:300', 'regex:'.self::YOUTUBE_URL],
            'video_popup_title' => ['nullable', 'string', 'max:150'],
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
            'video_popup_url.regex' => 'শুধু YouTube ভিডিওর লিংক দিন (youtube.com/watch?v=… বা youtu.be/…)।',
            'video_popup_url.required_if_accepted' => 'ভিডিও পপআপ চালু করতে YouTube লিংক দিন।',
            'ticker_link_url.regex' => 'লিংক অবশ্যই /, #, http://, https://, mailto: অথবা tel: দিয়ে শুরু হতে হবে।',
        ];
    }
}
