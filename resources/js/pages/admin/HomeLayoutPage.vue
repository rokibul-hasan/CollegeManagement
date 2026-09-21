<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { onBeforeRouteLeave } from 'vue-router';
import api, { errorMessage, fieldErrors } from '@/api';
import SchemaForm from '@/components/admin/SchemaForm.vue';
import TemplatePreviewModal from '@/components/admin/TemplatePreviewModal.vue';
import TemplateThumbnail from '@/components/admin/TemplateThumbnail.vue';
import HomeSections from '@/components/site/HomeSections.vue';
import { HOME_FIELDS, SECTION_TYPES, blankSection } from '@/data/sectionTypes';
import { can } from '@/stores/auth';
import { invalidateSite, loadSite } from '@/stores/site';
import { toast } from '@/stores/toast';

const payload = ref(null);
const template = ref('');
const items = ref([]);
const dirty = ref(false);
const saving = ref(false);
const busy = ref(false);
const errors = ref({});
const showPicker = ref(false);
const dragIndex = ref(null);
const showPreview = ref(true);
const previewing = ref(null);
// Bumped after every save so the template thumbnails reload with the saved layout.
const previewVersion = ref(Date.now());
// Expanded/collapsed state lives outside the section data so toggling it is not an unsaved change.
const openState = reactive({});
let nextUid = 1;
let tracking = false;

const current = computed(() => payload.value?.templates.find((item) => item.value === template.value));
const isDefault = computed(() => payload.value?.default_template === template.value);
const visibleCount = computed(() => items.value.filter((item) => item.is_active).length);
const visibleItems = computed(() => items.value.filter((item) => item.is_active));

watch(items, () => {
    if (tracking) {
        dirty.value = true;
    }
}, { deep: true });

const clone = (value) => JSON.parse(JSON.stringify(value));

function fieldsFor(section) {
    return [...(SECTION_TYPES[section.type]?.fields ?? []), ...HOME_FIELDS];
}

function wrap(section, isActive, open = false) {
    const uid = nextUid++;
    openState[uid] = open;

    return {
        uid,
        id: section.id ?? null,
        type: section.type,
        width: section.width || 'full',
        anchor: section.anchor ?? '',
        is_active: isActive,
        data: section.data ? clone(section.data) : blankSection(section.type),
    };
}

/**
 * Rebuild the editable list for the selected template from the last saved payload.
 */
function build() {
    tracking = false;
    const byId = Object.fromEntries(payload.value.sections.map((section) => [section.id, section]));

    items.value = payload.value.layouts[template.value].map((placement) => wrap(byId[placement.id], placement.is_active));
    errors.value = {};
    dirty.value = false;

    // Let the deep watcher see the rebuilt list before tracking edits again.
    queueMicrotask(() => {
        tracking = true;
    });
}

function selectTemplate(value) {
    if (value === template.value) {
        return;
    }

    if (dirty.value && !window.confirm('সংরক্ষণ না করা পরিবর্তন বাতিল হবে। অন্য টেমপ্লেটে যাবেন?')) {
        return;
    }

    template.value = value;
    build();
}

function move(index, direction) {
    const target = index + direction;

    if (target >= 0 && target < items.value.length) {
        const list = items.value;
        [list[index], list[target]] = [list[target], list[index]];
    }
}

function onDragEnter(index) {
    if (dragIndex.value === null || dragIndex.value === index) {
        return;
    }

    const [moved] = items.value.splice(dragIndex.value, 1);
    items.value.splice(index, 0, moved);
    dragIndex.value = index;
}

function addSection(type) {
    items.value.push(wrap({ type }, true, true));
    showPicker.value = false;
}

function duplicate(index) {
    const copy = items.value[index];
    items.value.splice(index + 1, 0, wrap({ type: copy.type, width: copy.width, data: copy.data }, copy.is_active, true));
}

function remove(index) {
    if (window.confirm('এই সেকশনটি সব টেমপ্লেট থেকে মুছে যাবে। শুধু এই টেমপ্লেটে লুকাতে চাইলে "দেখান" বন্ধ করুন। মুছবেন?')) {
        items.value.splice(index, 1);
    }
}

function summary(section) {
    const data = section.data;

    return data.heading || data.badge || data.widget || (data.html ? data.html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 50) : '') || '';
}

function sectionError(index) {
    return Object.entries(errors.value).find(([key]) => key.startsWith(`sections.${index}.`))?.[1];
}

function apply(data) {
    payload.value = data;
    build();
    invalidateSite();
    previewVersion.value = Date.now();
}

function editPreviewed() {
    selectTemplate(previewing.value.value);
    previewing.value = null;
}

async function makePreviewedDefault() {
    selectTemplate(previewing.value.value);

    if (template.value === previewing.value.value) {
        previewing.value = null;
        await makeDefault();
    }
}

async function save() {
    saving.value = true;
    errors.value = {};

    try {
        const { data } = await api.put(`/admin/home/${template.value}`, {
            sections: items.value.map(({ id, type, width, anchor, is_active, data: content }) => ({ id, type, width, anchor, is_active, data: content })),
        });
        apply(data);
        toast(`${current.value.label} টেমপ্লেট সংরক্ষণ হয়েছে।`);
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

async function resetPreset() {
    if (!window.confirm(`${current.value.label} টেমপ্লেটের সেকশনের ক্রম ও দেখান/লুকান শুরুর অবস্থায় ফিরবে। কনটেন্ট বদলাবে না। চালিয়ে যাবেন?`)) {
        return;
    }

    busy.value = true;

    try {
        apply((await api.post(`/admin/home/${template.value}/reset`)).data);
        toast('প্রিসেট ক্রম ফিরিয়ে আনা হয়েছে।');
    } catch (error) {
        toast(errorMessage(error), 'error');
    } finally {
        busy.value = false;
    }
}

async function makeDefault() {
    if (dirty.value && !window.confirm('সংরক্ষণ না করা পরিবর্তন আছে। সেগুলো ছাড়াই এই টেমপ্লেট ডিফল্ট করবেন?')) {
        return;
    }

    busy.value = true;

    try {
        const { data } = await api.post(`/admin/home/${template.value}/default`);
        payload.value.default_template = data.default_template;
        invalidateSite();
        previewVersion.value = Date.now();
        toast(`এখন থেকে সাইটে ${current.value.label} টেমপ্লেট দেখাবে।`);
    } catch (error) {
        toast(errorMessage(error), 'error');
    } finally {
        busy.value = false;
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

    const { data } = await api.get('/admin/home');
    payload.value = data;
    template.value = data.default_template;
    build();
});

onBeforeUnmount(() => window.removeEventListener('beforeunload', warnUnsaved));
</script>

<template>
    <form v-if="payload" style="display: grid; gap: 22px" @submit.prevent="save">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>সাইট টেমপ্লেট</h2>
                    <p>কনটেন্ট সব টেমপ্লেটে একই থাকে; প্রতিটি টেমপ্লেটের ডিজাইন, সেকশনের ক্রম ও দেখান/লুকান আলাদা।</p>
                </div>
            </div>
            <div class="panel-body" style="display: grid; gap: 16px">
                <div class="template-choices">
                    <div
                        v-for="option in payload.templates"
                        :key="option.value"
                        class="template-choice has-thumb"
                        :class="{ 'is-selected': option.value === template }"
                    >
                        <button type="button" class="template-thumb-btn" :title="`${option.label} বড় করে দেখুন`" @click="previewing = option">
                            <TemplateThumbnail :template="option.value" :version="previewVersion" />
                            <span class="template-thumb-zoom">⤢ বড় করে দেখুন</span>
                        </button>
                        <button type="button" class="template-choice-body" @click="selectTemplate(option.value)">
                            <span class="template-swatch" :data-swatch="option.value"><i /><i /><i /></span>
                            <b>
                                {{ option.label }}
                                <span v-if="option.value === payload.default_template" class="tag tag-gold">ডিফল্ট</span>
                            </b>
                            <small>{{ option.description }}</small>
                            <span class="template-choice-cta">{{ option.value === template ? '✓ সম্পাদনা করছেন' : 'সম্পাদনা করুন →' }}</span>
                        </button>
                    </div>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center">
                    <a :href="`/?template=${template}`" target="_blank" rel="noopener" class="btn btn-outline btn-sm">নতুন ট্যাবে সাইট দেখুন ↗</a>
                    <button type="button" class="btn btn-outline btn-sm" :disabled="busy" @click="resetPreset">প্রিসেট ক্রমে ফেরত নিন</button>
                    <span class="spacer" />
                    <span v-if="isDefault" style="font-size: 14px; color: var(--muted)">✓ এই টেমপ্লেটটি এখন সাইটে দেখাচ্ছে</span>
                    <button v-else-if="can('settings.manage')" type="button" class="btn btn-gold btn-sm" :disabled="busy" @click="makeDefault">
                        {{ current.label }} টেমপ্লেট ডিফল্ট করুন
                    </button>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>{{ current.label }} — হোমপেজের সেকশন</h2>
                    <p>টেনে এনে বা ▲▼ দিয়ে ক্রম বদলান। "দেখান" বন্ধ করলে শুধু এই টেমপ্লেটে লুকাবে। দেখাচ্ছে {{ visibleCount }}/{{ items.length }}টি।</p>
                </div>
            </div>
            <div class="panel-body" style="display: grid; gap: 12px">
                <div
                    v-for="(section, index) in items"
                    :key="section.uid"
                    class="section-card"
                    :class="{ 'is-inactive': !section.is_active, 'is-dragging': dragIndex === index }"
                    @dragenter.prevent="onDragEnter(index)"
                    @dragover.prevent
                >
                    <div class="section-bar">
                        <span
                            class="drag-handle"
                            draggable="true"
                            title="টেনে ক্রম বদলান"
                            @dragstart="dragIndex = index"
                            @dragend="dragIndex = null"
                        >⠿</span>
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
                        <label class="switch" style="font-size: 13px" title="এই টেমপ্লেটে দেখান/লুকান"><input v-model="section.is_active" type="checkbox"> দেখান</label>
                        <button type="button" class="btn-icon" :disabled="index === 0" aria-label="উপরে" @click="move(index, -1)">▲</button>
                        <button type="button" class="btn-icon" :disabled="index === items.length - 1" aria-label="নিচে" @click="move(index, 1)">▼</button>
                        <button type="button" class="btn-icon" title="কপি" @click="duplicate(index)">⧉</button>
                        <button type="button" class="btn-icon" title="মুছুন" style="color: var(--red)" @click="remove(index)">✕</button>
                    </div>
                    <div v-if="openState[section.uid]" class="section-body" style="display: grid; gap: 14px">
                        <div class="field" style="max-width: 360px">
                            <label>অ্যাংকর (ঐচ্ছিক)</label>
                            <input v-model.trim="section.anchor" class="input mono" placeholder="gallery">
                            <span class="hint">মেনুতে /#{{ section.anchor || 'anchor' }} দিলে এই সেকশনে যাবে।</span>
                        </div>
                        <SchemaForm :fields="fieldsFor(section)" :model="section.data" />
                    </div>
                    <div v-if="sectionError(index)" class="error" style="padding: 0 14px 12px; color: var(--red); font-size: 13px">{{ sectionError(index) }}</div>
                </div>

                <div v-if="!items.length" class="empty-state">হোমপেজে কোনো সেকশন নেই। নিচের বাটন দিয়ে যোগ করুন।</div>

                <div v-if="showPicker" class="section-picker">
                    <button v-for="(type, key) in SECTION_TYPES" :key="key" type="button" class="section-choice" @click="addSection(key)">
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

        <div class="panel" style="overflow: hidden">
            <div class="panel-head">
                <div>
                    <h2>লাইভ প্রিভিউ — {{ current.label }}</h2>
                    <p>সংরক্ষণের আগেই দেখুন; উপরের পরিবর্তন সাথে সাথে এখানে দেখাবে।</p>
                </div>
                <span class="spacer" />
                <button type="button" class="btn btn-outline btn-sm" @click="showPreview = !showPreview">{{ showPreview ? 'প্রিভিউ লুকান' : 'প্রিভিউ দেখান' }}</button>
            </div>
            <div v-if="showPreview" class="site-preview" :data-template="template">
                <HomeSections :sections="visibleItems" />
            </div>
        </div>

        <TemplatePreviewModal v-if="previewing" :template="previewing" :version="previewVersion" @close="previewing = null">
            <template #actions>
                <button v-if="previewing.value !== template" type="button" class="btn btn-outline btn-sm" @click="editPreviewed">সম্পাদনা করুন</button>
                <button
                    v-if="can('settings.manage') && previewing.value !== payload.default_template"
                    type="button"
                    class="btn btn-gold btn-sm"
                    :disabled="busy"
                    @click="makePreviewedDefault"
                >
                    ডিফল্ট করুন
                </button>
            </template>
        </TemplatePreviewModal>

        <div class="form-actions save-bar">
            <button type="submit" class="btn btn-primary" :disabled="saving" style="padding: 12px 28px">
                {{ saving ? 'সংরক্ষণ হচ্ছে…' : `${current.label} টেমপ্লেট সংরক্ষণ করুন` }}
            </button>
            <span v-if="dirty" style="align-self: center; font-size: 13.5px; color: var(--gold)">● সংরক্ষণ করা হয়নি</span>
        </div>
    </form>
    <div v-else class="empty-state">লোড হচ্ছে…</div>
</template>
