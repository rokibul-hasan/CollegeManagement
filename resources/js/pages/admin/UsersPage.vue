<script setup>
import { onMounted, reactive, ref } from 'vue';
import api, { errorMessage, fieldErrors } from '@/api';
import { auth } from '@/stores/auth';
import { toast } from '@/stores/toast';
import { bnDigits } from '@/utils/bn';

const users = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const roles = ref([]);
const filters = reactive({ q: '', role: '', page: 1 });
const editing = ref(null);
const errors = ref({});
const saving = ref(false);
const form = reactive({ name: '', email: '', role: '', password: '', password_confirmation: '' });

async function fetchUsers() {
    const { data } = await api.get('/admin/users', { params: filters });
    users.value = data.users.data;
    meta.value = data.users;
    roles.value = data.roles;
}

function applyFilters() {
    filters.page = 1;
    fetchUsers();
}

function openForm(user = null) {
    errors.value = {};
    editing.value = user ?? { id: null };
    Object.assign(form, {
        name: user?.name ?? '',
        email: user?.email ?? '',
        role: user?.role ?? roles.value[0]?.name ?? '',
        password: '',
        password_confirmation: '',
    });
}

async function save() {
    saving.value = true;
    errors.value = {};

    try {
        if (editing.value.id) {
            await api.put(`/admin/users/${editing.value.id}`, form);
        } else {
            await api.post('/admin/users', form);
        }

        toast('ইউজার সংরক্ষণ হয়েছে।');
        editing.value = null;
        fetchUsers();
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

async function remove(user) {
    if (!window.confirm(`"${user.name}" (${user.email}) ইউজারটি মুছে ফেলবেন?`)) {
        return;
    }

    try {
        await api.delete(`/admin/users/${user.id}`);
        toast('ইউজার মুছে ফেলা হয়েছে।');
        fetchUsers();
    } catch (error) {
        toast(errorMessage(error), 'error');
    }
}

onMounted(fetchUsers);
</script>

<template>
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); align-items: start">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>ইউজার</h2>
                    <p>মোট {{ bnDigits(meta.total) }} জন</p>
                </div>
                <span class="spacer" />
                <button type="button" class="btn btn-primary btn-sm" @click="openForm()">+ নতুন ইউজার</button>
            </div>
            <form class="panel-body" style="display: flex; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid var(--line-soft)" @submit.prevent="applyFilters">
                <input v-model.trim="filters.q" type="search" class="input" placeholder="নাম বা ইমেইল…" style="max-width: 260px; padding: 10px 12px">
                <select v-model="filters.role" class="input" style="max-width: 180px; padding: 10px 12px" @change="applyFilters">
                    <option value="">সকল রোল</option>
                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                </select>
                <button type="submit" class="btn btn-soft">খুঁজুন</button>
            </form>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>নাম</th><th>রোল</th><th /></tr></thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id">
                            <td>
                                <div style="font-weight: 600">{{ user.name }}</div>
                                <div style="font-size: 13px; color: var(--muted)">{{ user.email }}</div>
                            </td>
                            <td>
                                <span v-if="user.is_super_admin" class="tag tag-gold">super-admin</span>
                                <span v-else class="tag">{{ user.role ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-soft btn-sm" @click="openForm(user)">সম্পাদনা</button>
                                    <button
                                        v-if="!user.is_super_admin && user.id !== auth.user?.id"
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        @click="remove(user)"
                                    >
                                        মুছুন
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!users.length"><td colspan="3" class="empty-state">কোনো ইউজার পাওয়া যায়নি।</td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="meta.last_page > 1" class="pager" style="padding: 0 0 18px">
                <button
                    v-for="page in meta.last_page"
                    :key="page"
                    type="button"
                    :class="{ 'is-active': page === meta.current_page }"
                    @click="filters.page = page; fetchUsers()"
                >
                    {{ bnDigits(page) }}
                </button>
            </div>
        </div>

        <form v-if="editing" class="panel" @submit.prevent="save">
            <div class="panel-head"><h2>{{ editing.id ? 'ইউজার সম্পাদনা' : 'নতুন ইউজার' }}</h2></div>
            <div class="panel-body" style="display: grid; gap: 14px">
                <div class="field">
                    <label for="user-name">নাম *</label>
                    <input id="user-name" v-model="form.name" class="input" required>
                    <span v-if="errors.name" class="error">{{ errors.name }}</span>
                </div>
                <div class="field">
                    <label for="user-email">ইমেইল *</label>
                    <input id="user-email" v-model.trim="form.email" type="email" class="input" required :readonly="editing.is_super_admin">
                    <span v-if="errors.email" class="error">{{ errors.email }}</span>
                </div>
                <div v-if="!editing.is_super_admin" class="field">
                    <label for="user-role">রোল *</label>
                    <select id="user-role" v-model="form.role" class="input" required :disabled="editing.id === auth.user?.id">
                        <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                    </select>
                    <span class="hint">রোলের পারমিশন "রোল ও পারমিশন" পাতা থেকে ঠিক করুন।</span>
                    <span v-if="errors.role" class="error">{{ errors.role }}</span>
                </div>
                <div class="field">
                    <label for="user-password">পাসওয়ার্ড {{ editing.id ? '(পরিবর্তন না করলে খালি রাখুন)' : '*' }}</label>
                    <input id="user-password" v-model="form.password" type="password" class="input" autocomplete="new-password" :required="!editing.id">
                    <span v-if="errors.password" class="error">{{ errors.password }}</span>
                </div>
                <div class="field">
                    <label for="user-password2">পাসওয়ার্ড নিশ্চিত করুন</label>
                    <input id="user-password2" v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" :disabled="saving">সংরক্ষণ করুন</button>
                    <button type="button" class="btn btn-outline" @click="editing = null">বাতিল</button>
                </div>
            </div>
        </form>
    </div>
</template>
