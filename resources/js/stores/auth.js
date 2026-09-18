import { reactive } from 'vue';
import api from '@/api';

export const auth = reactive({
    user: null,
    checked: false,
});

/**
 * Whether the signed-in user holds a permission. The super admin holds everything.
 */
export function can(permission) {
    if (!auth.user) {
        return false;
    }

    return auth.user.is_super_admin || !permission || auth.user.permissions.includes(permission);
}

export function isSuperAdmin() {
    return Boolean(auth.user?.is_super_admin);
}

export async function checkAuth() {
    if (!auth.checked) {
        const { data } = await api.get('/admin/me');
        auth.user = data.user;
        auth.checked = true;
    }

    return auth.user;
}

export async function login(credentials) {
    const { data } = await api.post('/admin/login', credentials);
    auth.user = data.user;
    auth.checked = true;

    return auth.user;
}

export async function logout() {
    await api.post('/admin/logout');
    auth.user = null;
}
