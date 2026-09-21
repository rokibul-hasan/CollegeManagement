<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { onBeforeRouteLeave, useRoute, useRouter } from 'vue-router';
import api, { errorMessage, fieldErrors } from '@/api';
import SchemaForm from '@/components/admin/SchemaForm.vue';
import PageSections from '@/components/page-sections/PageSections.vue';
import PageHero from '@/components/site/PageHero.vue';
import { SECTION_TYPES, blankSection } from '@/data/sectionTypes';
import { loadSite } from '@/stores/site';
import { toast } from '@/stores/toast';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => Boolean(route.params.id));
const loaded = ref(false);
const saving = ref(false);
const dirty = ref(false);
const errors = ref({});
const showPicker = ref(false);
const showPreview = ref(false);

const page = reactive({ title: '', slug: '', lead: '', meta_description: '', layout: 'full', is_published: true });
const sections = ref([]);
// Expanded/collapsed state lives outside the section data so toggling it is not an unsaved change.
const openState = reactive({});
let nextUid = 1;

// Any change after the initial load marks the page as unsaved (including image uploads).
watch([page, sections], () => {
    if (loaded.value) {
        dirty.value = true;
    }
}, { deep: true });

const activeSections = computed(() => sections.value.filter((section) => section.is_active));

// Hero and intro blocks are designed for the home page only.
const pageSectionTypes = Object.fromEntries(Object.entries(SECTION_TYPES).filter(([, type]) => !type.homeOnly));

function wrap(section) {
    const uid = nextUid++;
    openState[uid] = section.open ?? false;

    return {
        uid,
        type: section.type,
        width: section.width || 'full',
        is_active: section.is_active ?? true,
        data: section.data || blankSection(section.type),
    };
}

function addSection(type) {
    sections.value.push(wrap({ type, open: true }));
    showPicker.value = false;
    dirty.value = true;
}

function duplicate(index) {
    const copy = JSON.parse(JSON.stringify(sections.value[index]));
    sections.value.splice(index + 1, 0, wrap({ ...copy, open: true }));
    dirty.value = true;
}

function move(index, direction) {
    const target = index + direction;

    if (target >= 0 && target < sections.value.length) {
        const list = sections.value;
        [list[index], list[target]] = [list[target], list[index]];
        dirty.value = true;
    }
}

function remove(index) {
    if (window.confirm('এই সেকশনটি মুছে ফেলবেন?')) {
        sections.value.splice(index, 1);
        dirty.value = true;
    }
}

function summary(section) {
    const data = section.data;

    return data.heading || data.widget || (data.html ? data.html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 50) : '') || '';
}

function sectionError(index) {
    return Object.entries(errors.value).find(([key]) => key.startsWith(`sections.${index}.`))?.[1];
}

async function save() {
    saving.value = true;
    errors.value = {};

    const payload = {
        ...page,
        sections: sections.value.map(({ type, width, is_active, data }) => ({ type, width, is_active, data })),
    };

    try {
        const { data } = isEdit.value
            ? await api.put(`/admin/pages/${route.params.id}`, payload)
            : await api.post('/admin/pages', payload);

        dirty.value = false;
        toast('পেজ সংরক্ষণ হয়েছে।');

        if (!isEdit.value) {
            router.replace(`/admin/pages/${data.id}/edit`);
        }
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

function warnUnsaved(event) {
    if (dirty.value) {
        event.preventDefault();
    }
}

onBeforeRouteLeave(() => !dirty.value || window.confirm('সংরক্ষণ না করা পরিবর্তন আছে। তবুও বের হবেন?'));

onMounted(async () => {
    window.addEventListener('beforeunload', warnUnsaved);
    loadSite();

    if (isEdit.value) {
        const { data } = await api.get(`/admin/pages/${route.params.id}`);
        Object.assign(page, {
            title: data.title,
            slug: data.slug,
            lead: data.lead ?? '',
            meta_description: data.meta_description ?? '',
            layout: data.layout,
            is_published: data.is_published,
        });
        sections.value = data.sections.map(wrap);
    }

    loaded.value = true;
    await nextTick();
    dirty.value = false;
});

onBeforeUnmount(() => window.removeEventListener('beforeunload', warnUnsaved));
</script>

<template>
    <form v-if="loaded" style="display: grid; gap: 22px" @submit.prevent="save">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>{{ isEdit ? 'পেজ সম্পাদনা' : 'নতুন পেজ' }}</h2>
                    <p>পেজের নাম, লিংক ও লেআউট</p>
                </div>
                <span class="spacer" />
                <a v-if="isEdit" :href="`/${page.slug}?preview=1`" target="_blank" rel="noopener" class="btn btn-outline btn-sm">সাইটে দেখুন ↗</a>
            </div>
            <div class="panel-body form-grid">
                <div class="field">
                    <label for="page-title">পেজের নাম *</label>
                    <input id="page-title" v-model="page.title" class="input" required>
                    <span v-if="errors.title" class="error">{{ errors.title }}</span>
                </div>
                <div class="field">
                    <label for="page-slug">লিংক (স্লাগ, ইংরেজিতে) *</label>
                    <input id="page-slug" v-model.trim="page.slug" class="input mono" required placeholder="library">
                    <span class="hint">পেজের ঠিকানা হবে: /{{ page.slug || 'slug' }} — মেনুতে যোগ করতে এই লিংক ব্যবহার করুন।</span>
                    <span v-if="errors.slug" class="error">{{ errors.slug }}</span>
                </div>
                <div class="field full">
                    <label for="page-lead">শিরোনামের নিচের লেখা</label>
                    <input id="page-lead" v-model="page.lead" class="input">
                </div>
                <div class="field">
                    <label for="page-layout">লেআউট</label>
                    <select id="page-layout" v-model="page.layout" class="input">
                        <option value="full">পূর্ণ প্রস্থ</option>
                        <option value="sidebar">ডানে সাইডবার (সাম্প্রতিক নোটিশ + গুরুত্বপূর্ণ লিংক)</option>
                    </select>
                </div>
                <div class="field">
                    <label for="page-meta">SEO বর্ণনা (ঐচ্ছিক)</label>
                    <input id="page-meta" v-model="page.meta_description" class="input" maxlength="300">
                </div>
                <label class="switch field full"><input v-model="page.is_published" type="checkbox"> প্রকাশিত (বন্ধ রাখলে শুধু অ্যাডমিনরা প্রিভিউ দেখতে পারবে)</label>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>সেকশনসমূহ</h2>
                    <p>উপর থেকে নিচে যে ক্রমে আছে, সাইটে সেভাবেই দেখাবে। "অর্ধেক" প্রস্থের পাশাপাশি দুটি সেকশন এক সারিতে বসে।</p>
                </div>
                <span class="spacer" />
                <button type="button" class="btn btn-outline btn-sm" @click="showPreview = !showPreview">{{ showPreview ? 'প্রিভিউ বন্ধ' : 'লাইভ প্রিভিউ' }}</button>
            </div>
            <div class="panel-body" style="display: grid; gap: 12px">
                <div v-for="(section, index) in sections" :key="section.uid" class="section-card" :class="{ 'is-inactive': !section.is_active }">
                    <div class="section-bar">
                        <button type="button" class="section-toggle" @click="openState[section.uid] = !openState[section.uid]">
                            <span class="section-icon">{{ SECTION_TYPES[section.type]?.icon }}</span>
                            <span>
                                <b>{{ SECTION_TYPES[section.type]?.label ?? section.type }}</b>
                                <span class="section-summary">{{ summary(section) }}</span>
                            </span>
                            <span class="nav-caret">{{ openState[section.uid] ? '▴' : '▾' }}</span>
                        </button>
                        <select v-model="section.width" class="input" style="width: auto; padding: 6px 8px; font-size: 13px" title="প্রস্থ">
                            <option value="full">পূর্ণ</option>
                            <option value="half">অর্ধেক</option>
                        </select>
                        <label class="switch" style="font-size: 13px" title="দেখান/লুকান"><input v-model="section.is_active" type="checkbox"> দেখান</label>
                        <button type="button" class="btn-icon" :disabled="index === 0" aria-label="উপরে" @click="move(index, -1)">▲</button>
                        <button type="button" class="btn-icon" :disabled="index === sections.length - 1" aria-label="নিচে" @click="move(index, 1)">▼</button>
                        <button type="button" class="btn-icon" title="কপি" @click="duplicate(index)">⧉</button>
                        <button type="button" class="btn-icon" title="মুছুন" style="color: var(--red)" @click="remove(index)">✕</button>
                    </div>
                    <div v-if="openState[section.uid]" class="section-body">
                        <SchemaForm :fields="SECTION_TYPES[section.type].fields" :model="section.data" />
                    </div>
                    <div v-if="sectionError(index)" class="error" style="padding: 0 14px 12px; color: var(--red); font-size: 13px">{{ sectionError(index) }}</div>
                </div>

                <div v-if="!sections.length" class="empty-state">এখনো কোনো সেকশন নেই। নিচের বাটন দিয়ে যোগ করুন।</div>

                <div v-if="showPicker" class="section-picker">
                    <button v-for="(type, key) in pageSectionTypes" :key="key" type="button" class="section-choice" @click="addSection(key)">
                        <span class="section-icon">{{ type.icon }}</span>
                        <b>{{ type.label }}</b>
                        <small>{{ type.description }}</small>
                    </button>
                </div>
                <div style="display: flex; gap: 10px">
                    <button type="button" class="btn btn-soft" @click="showPicker = !showPicker">{{ showPicker ? 'বাতিল' : '+ সেকশন যোগ করুন' }}</button>
                </div>
            </div>
        </div>

        <div v-if="showPreview" class="panel" style="overflow: hidden">
            <div class="panel-head"><h2>লাইভ প্রিভিউ</h2><p style="margin-left: 8px">সংরক্ষণের আগেই দেখুন</p></div>
            <div style="background: var(--bg)">
                <PageHero :title="page.title || 'পেজের নাম'" :lead="page.lead" />
                <div class="container page-body">
                    <PageSections :sections="activeSections" />
                </div>
            </div>
        </div>

        <div class="form-actions save-bar">
            <button type="submit" class="btn btn-primary" :disabled="saving" style="padding: 12px 28px">{{ saving ? 'সংরক্ষণ হচ্ছে…' : 'পেজ সংরক্ষণ করুন' }}</button>
            <router-link to="/admin/pages" class="btn btn-outline">পেজ তালিকা</router-link>
            <span v-if="dirty" style="align-self: center; font-size: 13.5px; color: var(--gold)">● সংরক্ষণ করা হয়নি</span>
        </div>
    </form>
    <div v-else class="empty-state">লোড হচ্ছে…</div>
</template>
