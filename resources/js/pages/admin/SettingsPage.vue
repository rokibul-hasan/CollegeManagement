<script setup>
import { onMounted, reactive, ref } from 'vue';
import api, { errorMessage, fieldErrors, toFormData } from '@/api';
import { invalidateSite } from '@/stores/site';
import { toast } from '@/stores/toast';

const FIELDS = [
    'site_name', 'site_name_en', 'site_location', 'eiin', 'phone', 'email', 'address',
    'ticker_text', 'footer_about', 'footer_copyright', 'facebook_url', 'youtube_url',
];

const form = reactive({ show_top_bar: true, notice_popup_enabled: true });
const logoUrl = ref(null);
const logoFile = ref(null);
const logoPreview = ref(null);
const removeLogo = ref(false);
const errors = ref({});
const saving = ref(false);
const loaded = ref(false);

function fill(data) {
    FIELDS.forEach((field) => {
        form[field] = data[field] ?? '';
    });
    form.show_top_bar = data.show_top_bar === '1';
    form.notice_popup_enabled = data.notice_popup_enabled === '1';
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
            </div>
            <div class="panel-body form-grid">
                <div class="field full">
                    <label for="ticker_text">"সর্বশেষ" টিকারের লেখা</label>
                    <input id="ticker_text" v-model="form.ticker_text" class="input">
                    <span class="hint">খালি রাখলে সর্বশেষ প্রকাশিত নোটিশের শিরোনাম দেখাবে।</span>
                </div>
                <label class="switch"><input v-model="form.show_top_bar" type="checkbox"> টপ বার দেখান (ইআইআইএন, ফোন, ইমেইল)</label>
                <label class="switch"><input v-model="form.notice_popup_enabled" type="checkbox"> হোমপেজে নোটিশ পপআপ দেখান</label>
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
