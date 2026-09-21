<script setup>
import { onMounted, reactive, ref } from 'vue';
import api, { errorMessage, fieldErrors, toFormData } from '@/api';
import TemplatePreviewModal from '@/components/admin/TemplatePreviewModal.vue';
import TemplateThumbnail from '@/components/admin/TemplateThumbnail.vue';
import { can } from '@/stores/auth';
import { invalidateSite } from '@/stores/site';
import { youtubeId, youtubeThumbnail } from '@/utils/youtube';
import { toast } from '@/stores/toast';

const FIELDS = [
    'site_name', 'site_name_en', 'site_location', 'eiin', 'phone', 'email', 'address',
    'ticker_text', 'ticker_label', 'ticker_link_label', 'ticker_link_url',
    'notice_popup_kicker', 'notice_popup_title',
    'notice_board_title', 'notice_board_tag', 'notice_board_more_label',
    'quick_links_title', 'recent_notices_title',
    'footer_about', 'footer_copyright', 'facebook_url', 'youtube_url', 'instagram_url', 'linkedin_url',
    'site_template', 'video_popup_url', 'video_popup_title',
];

const form = reactive({ show_top_bar: true, notice_popup_enabled: true, video_popup_enabled: false });
const logoUrl = ref(null);
const logoFile = ref(null);
const logoPreview = ref(null);
const removeLogo = ref(false);
const errors = ref({});
const saving = ref(false);
const loaded = ref(false);
const templateOptions = ref([]);
const previewing = ref(null);

function fill(data) {
    FIELDS.forEach((field) => {
        form[field] = data[field] ?? '';
    });
    form.show_top_bar = data.show_top_bar === '1';
    form.notice_popup_enabled = data.notice_popup_enabled === '1';
    form.video_popup_enabled = data.video_popup_enabled === '1';
    templateOptions.value = data.template_options ?? [];
    logoUrl.value = data.logo_url;
    logoFile.value = null;
    logoPreview.value = null;
    removeLogo.value = false;
}

function onLogoChange(event) {
    const file = event.target.files[0];

    if (file) {
        logoFile.value = file;
        logoPreview.value = URL.createObjectURL(file);
        removeLogo.value = false;
    }
}

async function save() {
    saving.value = true;
    errors.value = {};

    const payload = toFormData({ ...form, remove_logo: removeLogo.value });

    if (logoFile.value) {
        payload.append('logo', logoFile.value);
    }

    try {
        const { data } = await api.post('/admin/settings', payload);
        fill(data);
        invalidateSite();
        toast('সেটিংস সংরক্ষণ হয়েছে।');
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    fill((await api.get('/admin/settings')).data);
    loaded.value = true;
});
</script>

<template>
    <form v-if="loaded" style="display: grid; gap: 22px" @submit.prevent="save">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>লোগো ও প্রতিষ্ঠানের নাম</h2>
                    <p>হেডার, ফুটার ও ব্রাউজার ট্যাবে দেখাবে।</p>
                </div>
            </div>
            <div class="panel-body">
                <div style="display: flex; flex-wrap: wrap; gap: 18px; align-items: center; margin-bottom: 20px">
                    <div class="logo-preview">
                        <img v-if="logoPreview || (logoUrl && !removeLogo)" :src="logoPreview || logoUrl" alt="লোগো">
                        <span v-else class="placeholder-note">লোগো নেই</span>
                    </div>
                    <div class="field" style="flex: 1; min-width: 240px">
                        <label for="logo">লোগো আপলোড (PNG/JPG/WEBP, সর্বোচ্চ ২ MB)</label>
                        <input id="logo" type="file" accept="image/png,image/jpeg,image/webp" class="input" @change="onLogoChange">
                        <span v-if="errors.logo" class="error">{{ errors.logo }}</span>
                        <label v-if="logoUrl && !logoFile" class="switch" style="margin-top: 4px">
                            <input v-model="removeLogo" type="checkbox"> বর্তমান লোগো সরিয়ে ডিফল্ট লোগো ব্যবহার করুন
                        </label>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="site_name">প্রতিষ্ঠানের নাম (বাংলা) *</label>
                        <input id="site_name" v-model="form.site_name" class="input" required>
                        <span v-if="errors.site_name" class="error">{{ errors.site_name }}</span>
                    </div>
                    <div class="field">
                        <label for="site_name_en">প্রতিষ্ঠানের নাম (ইংরেজি)</label>
                        <input id="site_name_en" v-model="form.site_name_en" class="input">
                    </div>
                    <div class="field">
                        <label for="site_location">অবস্থান (নামের নিচে)</label>
                        <input id="site_location" v-model="form.site_location" class="input" placeholder="গোপালগঞ্জ, ঢাকা">
                    </div>
                    <div class="field">
                        <label for="eiin">ইআইআইএন</label>
                        <input id="eiin" v-model="form.eiin" class="input">
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>ওয়েবসাইট টেমপ্লেট</h2>
                    <p>সাইটের ডিজাইন বেছে নিন। প্রতিটি টেমপ্লেটের হোমপেজ সেকশনের ক্রম আলাদাভাবে সাজানো যায়।</p>
                </div>
                <span class="spacer" />
                <router-link v-if="can('pages.manage')" to="/admin/home" class="more-link">সেকশন সাজান →</router-link>
            </div>
            <div class="panel-body">
                <div class="template-choices">
                    <div
                        v-for="option in templateOptions"
                        :key="option.value"
                        class="template-choice has-thumb"
                        :class="{ 'is-selected': form.site_template === option.value }"
                    >
                        <button type="button" class="template-thumb-btn" :title="`${option.label} বড় করে দেখুন`" @click="previewing = option">
                            <TemplateThumbnail :template="option.value" />
                            <span class="template-thumb-zoom">⤢ বড় করে দেখুন</span>
                        </button>
                        <label class="template-choice-body">
                            <input v-model="form.site_template" type="radio" name="site_template" :value="option.value" class="sr-only">
                            <span class="template-swatch" :data-swatch="option.value"><i /><i /><i /></span>
                            <b>{{ option.label }}</b>
                            <small>{{ option.description }}</small>
                            <span class="template-choice-cta">{{ form.site_template === option.value ? '✓ নির্বাচিত' : 'নির্বাচন করুন' }}</span>
                        </label>
                    </div>
                </div>
                <p class="hint" style="margin: 12px 0 0">নির্বাচন করে নিচের "সংরক্ষণ করুন" চাপলে সাইটে এই টেমপ্লেট দেখাবে।</p>
                <TemplatePreviewModal v-if="previewing" :template="previewing" @close="previewing = null">
                    <template #actions>
                        <button type="button" class="btn btn-gold btn-sm" @click="form.site_template = previewing.value; previewing = null">এটি নির্বাচন করুন</button>
                    </template>
                </TemplatePreviewModal>
                <span v-if="errors.site_template" class="error">{{ errors.site_template }}</span>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>যোগাযোগ</h2>
                    <p>টপ বার, ফুটার ও যোগাযোগ পাতায় দেখাবে।</p>
                </div>
            </div>
            <div class="panel-body form-grid">
                <div class="field">
                    <label for="phone">ফোন</label>
                    <input id="phone" v-model="form.phone" class="input">
                </div>
                <div class="field">
                    <label for="email">ইমেইল</label>
                    <input id="email" v-model="form.email" type="email" class="input">
                    <span v-if="errors.email" class="error">{{ errors.email }}</span>
                </div>
                <div class="field full">
                    <label for="address">ঠিকানা</label>
                    <input id="address" v-model="form.address" class="input">
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>হেডার ও নোটিশ</h2>
                    <p>টপ বার, সর্বশেষ টিকার ও নোটিশ পপআপ নিয়ন্ত্রণ।</p>
                </div>
                <span class="spacer" />
                <router-link v-if="can('menus.manage')" to="/admin/menus" class="more-link">টপ বার ও হেডারের বাটন →</router-link>
            </div>
            <div class="panel-body form-grid">
                <div class="field full">
                    <label for="ticker_text">টিকারের লেখা</label>
                    <input id="ticker_text" v-model="form.ticker_text" class="input">
                    <span class="hint">খালি রাখলে সর্বশেষ প্রকাশিত নোটিশের শিরোনাম দেখাবে।</span>
                </div>
                <div class="field">
                    <label for="ticker_label">টিকারের লাল লেবেল</label>
                    <input id="ticker_label" v-model="form.ticker_label" class="input" placeholder="সর্বশেষ">
                    <span class="hint">খালি রাখলে লেবেলটি দেখাবে না।</span>
                </div>
                <div class="field">
                    <label for="ticker_link_label">টিকারের লিংকের লেখা</label>
                    <input id="ticker_link_label" v-model="form.ticker_link_label" class="input" placeholder="দেখুন →">
                </div>
                <div class="field full">
                    <label for="ticker_link_url">টিকারের লিংক</label>
                    <input id="ticker_link_url" v-model.trim="form.ticker_link_url" class="input mono" placeholder="/notices">
                    <span v-if="errors.ticker_link_url" class="error">{{ errors.ticker_link_url }}</span>
                </div>
                <label class="switch"><input v-model="form.show_top_bar" type="checkbox"> টপ বার দেখান (ইআইআইএন, ফোন, ইমেইল)</label>
                <label class="switch"><input v-model="form.notice_popup_enabled" type="checkbox"> হোমপেজে নোটিশ পপআপ দেখান</label>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>সাইটের শিরোনাম ও লেবেল</h2>
                    <p>নোটিশ বোর্ড, গুরুত্বপূর্ণ লিংক ও পপআপে যে স্থায়ী লেখাগুলো দেখায়।</p>
                </div>
            </div>
            <div class="panel-body form-grid">
                <div class="field">
                    <label for="notice_board_title">নোটিশ বোর্ডের শিরোনাম</label>
                    <input id="notice_board_title" v-model="form.notice_board_title" class="input" placeholder="নোটিশ বোর্ড">
                </div>
                <div class="field">
                    <label for="notice_board_tag">নোটিশ বোর্ডের ট্যাগ</label>
                    <input id="notice_board_tag" v-model="form.notice_board_tag" class="input" placeholder="NOTICE">
                </div>
                <div class="field">
                    <label for="notice_board_more_label">নোটিশ বোর্ডের নিচের লিংক</label>
                    <input id="notice_board_more_label" v-model="form.notice_board_more_label" class="input" placeholder="সব নোটিশ দেখুন →">
                </div>
                <div class="field">
                    <label for="quick_links_title">"গুরুত্বপূর্ণ লিংক" বক্সের শিরোনাম</label>
                    <input id="quick_links_title" v-model="form.quick_links_title" class="input" placeholder="গুরুত্বপূর্ণ লিংক">
                </div>
                <div class="field">
                    <label for="recent_notices_title">"সাম্প্রতিক নোটিশ" শিরোনাম</label>
                    <input id="recent_notices_title" v-model="form.recent_notices_title" class="input" placeholder="সাম্প্রতিক নোটিশ">
                </div>
                <div class="field">
                    <label for="notice_popup_kicker">নোটিশ পপআপের ছোট লেখা</label>
                    <input id="notice_popup_kicker" v-model="form.notice_popup_kicker" class="input" placeholder="জরুরি বিজ্ঞপ্তি">
                </div>
                <div class="field">
                    <label for="notice_popup_title">নোটিশ পপআপের শিরোনাম</label>
                    <input id="notice_popup_title" v-model="form.notice_popup_title" class="input" placeholder="সাম্প্রতিক নোটিশ">
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>ভিডিও পপআপ</h2>
                    <p>সাইটের নিচে বাম কোণে ছোট ভিডিও কার্ড ভাসবে; ক্লিক করলে বড় পপআপে ভিডিও চলবে।</p>
                </div>
            </div>
            <div class="panel-body form-grid">
                <label class="switch field full"><input v-model="form.video_popup_enabled" type="checkbox"> ভিডিও পপআপ দেখান</label>
                <div class="field">
                    <label for="video_popup_url">YouTube ভিডিও লিংক</label>
                    <input id="video_popup_url" v-model.trim="form.video_popup_url" type="url" class="input" placeholder="https://www.youtube.com/watch?v=…">
                    <span class="hint">youtube.com/watch?v=…, youtu.be/…, shorts বা live লিংক চলবে।</span>
                    <span v-if="errors.video_popup_url" class="error">{{ errors.video_popup_url }}</span>
                </div>
                <div class="field">
                    <label for="video_popup_title">কার্ডের শিরোনাম</label>
                    <input id="video_popup_title" v-model="form.video_popup_title" class="input" placeholder="কলেজ পরিচিতি ভিডিও">
                </div>
                <div v-if="youtubeId(form.video_popup_url)" class="field full">
                    <span class="label">প্রিভিউ</span>
                    <img :src="youtubeThumbnail(youtubeId(form.video_popup_url))" alt="ভিডিওর ছবি" style="width: 240px; aspect-ratio: 16 / 9; object-fit: cover; border-radius: 8px; border: 1px solid var(--line)">
                </div>
                <span v-else-if="form.video_popup_url" class="error field full">এটি সঠিক YouTube লিংক মনে হচ্ছে না।</span>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>ফুটার</h2>
                    <p>ফুটারের কলাম ও লিংক "মেনু ও ফুটার লিংক" পাতা থেকে পরিবর্তন করুন।</p>
                </div>
                <span class="spacer" />
                <router-link to="/admin/menus" class="more-link">ফুটার লিংক →</router-link>
            </div>
            <div class="panel-body form-grid">
                <div class="field full">
                    <label for="footer_about">সংক্ষিপ্ত পরিচিতি</label>
                    <textarea id="footer_about" v-model="form.footer_about" class="input" style="min-height: 80px" />
                </div>
                <div class="field full">
                    <label for="footer_copyright">কপিরাইট লেখা</label>
                    <input id="footer_copyright" v-model="form.footer_copyright" class="input">
                </div>
                <div class="field">
                    <label for="facebook_url">ফেসবুক পেজ লিংক</label>
                    <input id="facebook_url" v-model.trim="form.facebook_url" type="url" class="input" placeholder="https://facebook.com/…">
                    <span v-if="errors.facebook_url" class="error">{{ errors.facebook_url }}</span>
                </div>
                <div class="field">
                    <label for="youtube_url">ইউটিউব চ্যানেল লিংক</label>
                    <input id="youtube_url" v-model.trim="form.youtube_url" type="url" class="input" placeholder="https://youtube.com/…">
                    <span v-if="errors.youtube_url" class="error">{{ errors.youtube_url }}</span>
                </div>
                <div class="field">
                    <label for="instagram_url">ইনস্টাগ্রাম লিংক</label>
                    <input id="instagram_url" v-model.trim="form.instagram_url" type="url" class="input" placeholder="https://instagram.com/…">
                    <span v-if="errors.instagram_url" class="error">{{ errors.instagram_url }}</span>
                </div>
                <div class="field">
                    <label for="linkedin_url">লিংকডইন লিংক</label>
                    <input id="linkedin_url" v-model.trim="form.linkedin_url" type="url" class="input" placeholder="https://linkedin.com/…">
                    <span v-if="errors.linkedin_url" class="error">{{ errors.linkedin_url }}</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving" style="padding: 12px 28px">
                {{ saving ? 'সংরক্ষণ হচ্ছে…' : 'সকল সেটিংস সংরক্ষণ করুন' }}
            </button>
        </div>
    </form>
    <div v-else class="empty-state">লোড হচ্ছে…</div>
</template>
