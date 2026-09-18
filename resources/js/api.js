import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
    withXSRFToken: true,
});

/**
 * Turn a Laravel error response into a single readable message.
 */
export function errorMessage(error, fallback = 'কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।') {
    const data = error?.response?.data;

    if (data?.errors) {
        return Object.values(data.errors).flat()[0];
    }

    return data?.message || fallback;
}

/**
 * Validation errors keyed by field, each reduced to its first message.
 */
export function fieldErrors(error) {
    const errors = error?.response?.data?.errors ?? {};

    return Object.fromEntries(Object.entries(errors).map(([field, messages]) => [field, messages[0]]));
}

/**
 * Build multipart form data, turning booleans into 1/0 so Laravel's boolean rule accepts them.
 */
export function toFormData(values) {
    const form = new FormData();

    Object.entries(values).forEach(([key, value]) => {
        if (value === null || value === undefined) {
            form.append(key, '');
        } else if (typeof value === 'boolean') {
            form.append(key, value ? '1' : '0');
        } else {
            form.append(key, value);
        }
    });

    return form;
}

export default api;
