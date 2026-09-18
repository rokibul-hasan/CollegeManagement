<script setup>
import { computed, onMounted, ref } from 'vue';
import api, { errorMessage, fieldErrors } from '@/api';
import { checkAuth, auth } from '@/stores/auth';
import { toast } from '@/stores/toast';
import { bnDigits } from '@/utils/bn';

const roles = ref([]);
const permissions = ref([]);
const grantable = ref([]);
const selectedId = ref(null);
const draft = ref([]);
const newRoleName = ref('');
const errors = ref({});

const selected = computed(() => roles.value.find((role) => role.id === selectedId.value));

async function fetchRoles() {
    const { data } = await api.get('/admin/roles');
    roles.value = data.roles;
    permissions.value = data.permissions;
    grantable.value = data.grantable;

    if (!selected.value) {
        select(roles.value.find((role) => !role.is_locked) ?? roles.value[0]);
    }
}

function select(role) {
    selectedId.value = role?.id ?? null;
    draft.value = [...(role?.permissions ?? [])];
}

async function savePermissions() {
    try {
        await api.put(`/admin/roles/${selected.value.id}`, { permissions: draft.value });
        toast('পারমিশন সংরক্ষণ হয়েছে।');
        await fetchRoles();
        select(selected.value);

        // The signed-in user's own role may have changed.
        auth.checked = false;
        await checkAuth();
    } catch (error) {
        toast(errorMessage(error), 'error');
    }
}

async function createRole() {
    errors.value = {};

    try {
        const { data } = await api.post('/admin/roles', { name: newRoleName.value, permissions: [] });
        newRoleName.value = '';
        toast('নতুন রোল তৈরি হয়েছে। এখন পারমিশন বাছাই করুন।');
        await fetchRoles();
        select(roles.value.find((role) => role.id === data.id));
    } catch (error) {
        errors.value = fieldErrors(error);
        toast(errorMessage(error), 'error');
    }
}

async function removeRole(role) {
    if (!window.confirm(`"${role.name}" রোলটি মুছে ফেলবেন?`)) {
        return;
    }

    try {
        await api.delete(`/admin/roles/${role.id}`);
        toast('রোল মুছে ফেলা হয়েছে।');
        selectedId.value = null;
        fetchRoles();
    } catch (error) {
        toast(errorMessage(error), 'error');
    }
}

onMounted(fetchRoles);
</script>

<template>
    <div class="grid" style="grid-template-columns: minmax(260px, 340px) minmax(0, 1fr); align-items: start">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>রোল</h2>
                    <p>একটি রোল বেছে নিয়ে তার পারমিশন ঠিক করুন।</p>
                </div>
            </div>
            <div class="panel-body" style="display: grid; gap: 6px">
                <button
                    v-for="role in roles"
                    :key="role.id"
                    type="button"
                    class="tab"
                    style="display: flex; justify-content: space-between; text-align: left"
                    :class="{ 'is-active': role.id === selectedId }"
                    @click="select(role)"
                >
                    <span>{{ role.name }} <span v-if="role.is_locked">🔒</span></span>
                    <span class="mono" style="font-size: 12px; opacity: .75">{{ bnDigits(role.users_count) }} জন</span>
                </button>
            </div>
            <form class="panel-body" style="border-top: 1px solid var(--line-soft); display: grid; gap: 8px" @submit.prevent="createRole">
                <label for="role-name" class="label" style="font-size: 14px; font-weight: 600">নতুন রোল</label>
                <div style="display: flex; gap: 8px">
                    <input id="role-name" v-model.trim="newRoleName" class="input mono" placeholder="accountant" required style="padding: 10px 12px">
                    <button type="submit" class="btn btn-primary">যোগ</button>
                </div>
                <span v-if="errors.name" class="error" style="font-size: 13px; color: var(--red)">{{ errors.name }}</span>
            </form>
        </div>

        <div v-if="selected" class="panel">
            <div class="panel-head">
                <div>
                    <h2>{{ selected.name }}</h2>
                    <p v-if="selected.is_locked">সুপার অ্যাডমিন সবকিছু করতে পারে — এই রোল পরিবর্তন করা যায় না।</p>
                    <p v-else>টিক দেওয়া কাজগুলো এই রোলের ইউজাররা করতে পারবে।</p>
                </div>
                <span class="spacer" />
                <button v-if="!selected.is_system" type="button" class="btn btn-danger btn-sm" @click="removeRole(selected)">রোল মুছুন</button>
            </div>
            <div class="panel-body" style="display: grid; gap: 12px">
                <label
                    v-for="permission in permissions"
                    :key="permission.name"
                    class="switch"
                    :style="{ opacity: grantable.includes(permission.name) ? 1 : 0.5 }"
                >
                    <input
                        v-model="draft"
                        type="checkbox"
                        :value="permission.name"
                        :disabled="selected.is_locked || !grantable.includes(permission.name)"
                    >
                    <span>
                        {{ permission.label }}
                        <span class="mono" style="font-size: 12px; color: var(--muted); margin-left: 6px">{{ permission.name }}</span>
                    </span>
                </label>
                <p v-if="!selected.permissions.includes('admin.access') && !draft.includes('admin.access') && !selected.is_locked" class="hint" style="margin: 0; font-size: 13px; color: var(--muted)">
                    "অ্যাডমিন প্যানেলে প্রবেশ" না থাকলে এই রোলের ইউজার অ্যাডমিন প্যানেলে লগইন করতে পারবে না (যেমন শিক্ষক/শিক্ষার্থী)।
                </p>
                <div v-if="!selected.is_locked" class="form-actions">
                    <button type="button" class="btn btn-primary" @click="savePermissions">পারমিশন সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    </div>
</template>
