<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api, { errorMessage, fieldErrors } from '@/api';
import { invalidateSite } from '@/stores/site';
import { toast } from '@/stores/toast';

const LOCATION_HELP = {
    main: 'হেডারের প্রধান মেনু। কোনো আইটেমের নিচে সাব-মেনু যোগ করলে সেটি ড্রপডাউন হিসেবে দেখাবে।',
    footer: 'প্রতিটি মূল আইটেম ফুটারের একটি কলামের শিরোনাম; তার সাব-আইটেমগুলো কলামের লিংক।',
    quick: 'হোমপেজের "গুরুত্বপূর্ণ লিংক" বক্স ও ভেতরের পাতার সাইডবারে দেখাবে।',
};

const location = ref('main');
const locations = ref({});
const menus = ref([]);
const errors = ref({});
const editing = ref(null);
const form = reactive({ label: '', url: '', parent_id: '', open_in_new_tab: false, is_active: true });

const parentOptions = computed(() => menus.value.filter((menu) => menu.id !== editing.value?.id));

async function fetchMenus() {
    const { data } = await api.get('/admin/menus', { params: { location: location.value } });
    locations.value = data.locations;
    menus.value = data.menus;
}

function switchLocation(key) {
    location.value = key;
    closeForm();
    fetchMenus();
}

function openForm(menu = null, parentId = '') {
    errors.value = {};
    editing.value = menu ?? { id: null };
    Object.assign(form, menu
        ? { label: menu.label, url: menu.url ?? '', parent_id: menu.parent_id ?? '', open_in_new_tab: menu.open_in_new_tab, is_active: menu.is_active }
        : { label: '', url: '', parent_id: parentId, open_in_new_tab: false, is_active: true });
}

function closeForm() {
    editing.value = null;
}

async function save() {
    errors.value = {};
    const payload = { ...form, location: location.value, parent_id: form.parent_id || null, url: form.url || null };

    try {
        if (editing.value.id) {
            await api.put(`/admin/menus/${editing.value.id}`, payload);
        } else {
            await api.post('/admin/menus', payload);
        }

        invalidateSite();
        toast('মেনু সংরক্ষণ হয়েছে।');
        closeForm();
        fetchMenus();
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    }
}

async function remove(menu) {
    const extra = menu.children?.length ? ' এর সকল সাব-মেনুও মুছে যাবে।' : '';

    if (!window.confirm(`"${menu.label}" মুছবেন?${extra}`)) {
        return;
    }

    await api.delete(`/admin/menus/${menu.id}`);
    invalidateSite();
    toast('মেনু মুছে ফেলা হয়েছে।');
    fetchMenus();
}

async function move(list, index, direction) {
    const target = index + direction;

    if (target < 0 || target >= list.length) {
        return;
    }

    [list[index], list[target]] = [list[target], list[index]];

    try {
        await api.post('/admin/menus/reorder', { ids: list.map((item) => item.id) });
        invalidateSite();
    } catch (error) {
        toast(errorMessage(error), 'error');
        fetchMenus();
    }
}

onMounted(fetchMenus);
</script>

<template>
    <div class="tabs">
        <button
            v-for="(label, key) in locations"
            :key="key"
            type="button"
            class="tab"
            :class="{ 'is-active': location === key }"
            @click="switchLocation(key)"
        >
            {{ label }}
        </button>
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); align-items: start">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>{{ locations[location] }}</h2>
                    <p>{{ LOCATION_HELP[location] }}</p>
                </div>
                <span class="spacer" />
                <button type="button" class="btn btn-primary btn-sm" @click="openForm()">+ নতুন আইটেম</button>
            </div>
            <div class="panel-body">
                <div class="menu-tree">
                    <div v-for="(menu, index) in menus" :key="menu.id" class="menu-node">
                        <div class="menu-line" :class="{ 'is-inactive': !menu.is_active }">
                            <div style="display: flex; flex-direction: column; gap: 2px">
                                <button type="button" class="btn-icon" style="height: 20px" :disabled="index === 0" aria-label="উপরে" @click="move(menus, index, -1)">▲</button>
                                <button type="button" class="btn-icon" style="height: 20px" :disabled="index === menus.length - 1" aria-label="নিচে" @click="move(menus, index, 1)">▼</button>
                            </div>
                            <div style="min-width: 0; flex: 1; display: grid">
                                <span class="label">{{ menu.label }}</span>
                                <span class="url">{{ menu.url || '— লিংক নেই —' }}</span>
                            </div>
                            <div class="actions" style="display: flex; gap: 6px">
                                <button type="button" class="btn btn-soft btn-sm" title="সাব-মেনু যোগ" @click="openForm(null, menu.id)">+ সাব</button>
                                <button type="button" class="btn btn-soft btn-sm" @click="openForm(menu)">সম্পাদনা</button>
                                <button type="button" class="btn btn-danger btn-sm" @click="remove(menu)">মুছুন</button>
                            </div>
                        </div>
                        <div v-if="menu.children.length" class="menu-children">
                            <div v-for="(child, childIndex) in menu.children" :key="child.id" class="menu-line" :class="{ 'is-inactive': !child.is_active }">
                                <button type="button" class="btn-icon" style="width: 26px; height: 26px" :disabled="childIndex === 0" aria-label="উপরে" @click="move(menu.children, childIndex, -1)">▲</button>
                                <button type="button" class="btn-icon" style="width: 26px; height: 26px" :disabled="childIndex === menu.children.length - 1" aria-label="নিচে" @click="move(menu.children, childIndex, 1)">▼</button>
                                <div style="min-width: 0; flex: 1; display: grid">
                                    <span class="label" style="font-size: 14.5px">{{ child.label }}</span>
                                    <span class="url">{{ child.url || '—' }}</span>
                                </div>
                                <button type="button" class="btn btn-soft btn-sm" @click="openForm(child)">সম্পাদনা</button>
                                <button type="button" class="btn btn-danger btn-sm" @click="remove(child)">মুছুন</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="!menus.length" class="empty-state">এই অবস্থানে কোনো মেনু নেই।</div>
                </div>
            </div>
        </div>

        <form v-if="editing" class="panel" @submit.prevent="save">
            <div class="panel-head">
                <h2>{{ editing.id ? 'মেনু সম্পাদনা' : 'নতুন মেনু আইটেম' }}</h2>
            </div>
            <div class="panel-body" style="display: grid; gap: 14px">
                <div class="field">
                    <label for="menu-label">লেবেল *</label>
                    <input id="menu-label" v-model="form.label" class="input" required>
                    <span v-if="errors.label" class="error">{{ errors.label }}</span>
                </div>
                <div class="field">
                    <label for="menu-url">লিংক</label>
                    <input id="menu-url" v-model.trim="form.url" class="input mono" placeholder="/about অথবা https://…">
                    <span class="hint">সাইটের পাতা: /about, /teachers, /notices?category=exam · বাইরের সাইট: https://…</span>
                    <span v-if="errors.url" class="error">{{ errors.url }}</span>
                </div>
                <div class="field">
                    <label for="menu-parent">মূল মেনু</label>
                    <select id="menu-parent" v-model="form.parent_id" class="input">
                        <option value="">— এটি নিজেই মূল মেনু —</option>
                        <option v-for="option in parentOptions" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                    <span v-if="errors.parent_id" class="error">{{ errors.parent_id }}</span>
                </div>
                <label class="switch"><input v-model="form.is_active" type="checkbox"> সক্রিয় (ওয়েবসাইটে দেখাবে)</label>
                <label class="switch"><input v-model="form.open_in_new_tab" type="checkbox"> নতুন ট্যাবে খুলবে</label>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
                    <button type="button" class="btn btn-outline" @click="closeForm">বাতিল</button>
                </div>
            </div>
        </form>
    </div>
</template>
