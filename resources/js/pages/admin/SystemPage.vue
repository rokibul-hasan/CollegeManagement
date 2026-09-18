<script setup>
import { onMounted, ref } from 'vue';
import api, { errorMessage } from '@/api';
import { toast } from '@/stores/toast';
import { bnDigits } from '@/utils/bn';

const status = ref(null);
const running = ref('');
const output = ref('');

async function fetchStatus() {
    status.value = (await api.get('/admin/system')).data;
}

async function run(action, confirmText) {
    if (!window.confirm(confirmText)) {
        return;
    }

    running.value = action;
    output.value = '';

    try {
        const { data } = await api.post(`/admin/system/${action}`);
        output.value = data.output;
        toast(action === 'migrate' ? 'মাইগ্রেশন সম্পন্ন হয়েছে।' : 'ক্যাশ পরিষ্কার হয়েছে।');
    } catch (error) {
        output.value = error?.response?.data?.output ?? errorMessage(error);
        toast('কমান্ড ব্যর্থ হয়েছে। নিচের আউটপুট দেখুন।', 'error');
    } finally {
        running.value = '';
        fetchStatus();
    }
}

onMounted(fetchStatus);
</script>

<template>
    <template v-if="status">
        <div class="stat-grid">
            <div class="stat"><small>PHP</small><b style="font-size: 22px">{{ status.php }}</b></div>
            <div class="stat"><small>Laravel</small><b style="font-size: 22px">{{ status.laravel }}</b></div>
            <div class="stat">
                <small>ডাটাবেস ({{ status.database.driver }})</small>
                <b style="font-size: 20px" :style="{ color: status.database.connected ? 'var(--navy)' : 'var(--red)' }">
                    {{ status.database.connected ? 'সংযুক্ত' : 'সংযোগ নেই' }}
                </b>
            </div>
            <div class="stat" :class="{ accent: status.pending_migrations.length }">
                <small>বাকি মাইগ্রেশন</small>
                <b>{{ bnDigits(status.pending_migrations.length) }}</b>
            </div>
        </div>

        <div class="grid grid-auto-300" style="align-items: start">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>ডাটাবেস মাইগ্রেশন</h2>
                        <p>নতুন কোড আপলোডের পর নতুন টেবিল/কলাম তৈরি করতে চালান।</p>
                    </div>
                </div>
                <div class="panel-body" style="display: grid; gap: 12px">
                    <div v-if="status.pending_migrations.length" style="display: grid; gap: 4px">
                        <div v-for="migration in status.pending_migrations" :key="migration" class="mono" style="font-size: 12.5px; color: var(--text)">• {{ migration }}</div>
                    </div>
                    <p v-else style="margin: 0; color: var(--muted); font-size: 14.5px">✔ সব মাইগ্রেশন চালানো আছে।</p>
                    <div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="!!running || !status.pending_migrations.length"
                            @click="run('migrate', 'ডাটাবেস মাইগ্রেশন চালাবেন? চালানোর আগে ডাটাবেসের ব্যাকআপ রাখা ভালো।')"
                        >
                            {{ running === 'migrate' ? 'চলছে…' : 'মাইগ্রেশন চালান' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>ক্যাশ পরিষ্কার</h2>
                        <p>কনফিগ, রাউট, ভিউ, অ্যাপ ও পারমিশন ক্যাশ মুছে ফেলে।</p>
                    </div>
                </div>
                <div class="panel-body" style="display: grid; gap: 12px">
                    <p style="margin: 0; color: var(--muted); font-size: 14.5px">
                        .env বদলানো বা নতুন ফাইল আপলোডের পর পরিবর্তন না দেখালে চালান। ক্যাশ স্টোর: <span class="mono">{{ status.cache_store }}</span>
                    </p>
                    <div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="!!running"
                            @click="run('clear-cache', 'সব ক্যাশ পরিষ্কার করবেন?')"
                        >
                            {{ running === 'clear-cache' ? 'চলছে…' : 'ক্যাশ পরিষ্কার করুন' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>সার্ভার অবস্থা</h2></div>
            <div class="panel-body" style="display: grid; gap: 8px; font-size: 14.5px">
                <div>পরিবেশ: <b class="mono">{{ status.environment }}</b></div>
                <div>
                    ডিবাগ মোড:
                    <b :style="{ color: status.debug ? 'var(--red)' : 'var(--navy)' }">{{ status.debug ? 'চালু (লাইভ সাইটে বন্ধ রাখুন!)' : 'বন্ধ' }}</b>
                </div>
                <div v-for="(ok, folder) in status.writable" :key="folder">
                    <span class="mono">{{ folder }}</span> লেখার অনুমতি:
                    <b :style="{ color: ok ? 'var(--navy)' : 'var(--red)' }">{{ ok ? 'আছে' : 'নেই — পারমিশন 755/775 দিন' }}</b>
                </div>
            </div>
        </div>

        <div v-if="output" class="panel">
            <div class="panel-head"><h2>আউটপুট</h2></div>
            <pre class="panel-body mono" style="margin: 0; white-space: pre-wrap; font-size: 12.5px; background: var(--navy-ink); color: #d3e2f2; border-radius: 0 0 10px 10px">{{ output }}</pre>
        </div>
    </template>
    <div v-else class="empty-state">লোড হচ্ছে…</div>
</template>
