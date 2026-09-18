<script setup>
import { computed } from 'vue';
import SmartLink from '@/components/site/SmartLink.vue';
import { safeUrl } from '@/utils/html';

const props = defineProps({
    data: { type: Object, required: true },
});

const split = (value) => String(value || '').split('|').map((cell) => cell.trim());

const headers = computed(() => (props.data.columns ? split(props.data.columns) : []));

const rows = computed(() => (props.data.rows || []).map((row) => ({
    cells: split(row.cells),
    link: safeUrl(row.link_url) ? { url: safeUrl(row.link_url), label: row.link_label || 'দেখুন' } : null,
})));

const columnCount = computed(() => Math.max(
    headers.value.length,
    ...rows.value.map((row) => row.cells.length + (row.link ? 1 : 0)),
    1,
));
</script>

<template>
    <div v-if="data.style === 'list'" class="card fee-card">
        <h3 v-if="data.heading">{{ data.heading }}</h3>
        <p v-if="data.lead" class="sub">{{ data.lead }}</p>
        <div v-for="(row, index) in rows" :key="index" class="fee-row">
            <span>{{ row.cells[0] }}</span>
            <span>
                {{ row.cells.slice(1).join(' · ') }}
                <SmartLink v-if="row.link" :to="row.link.url" :new-tab="/^https?:/i.test(row.link.url)">{{ row.link.label }}</SmartLink>
            </span>
        </div>
        <p v-if="data.note" class="placeholder-note" style="margin: 16px 0 0">{{ data.note }}</p>
    </div>

    <div v-else>
        <h3 v-if="data.heading" class="bold-title">{{ data.heading }}</h3>
        <p v-if="data.lead" class="ps-lead">{{ data.lead }}</p>
        <div class="table-card" style="overflow-x: auto">
            <div class="data-grid" :style="{ gridTemplateColumns: `repeat(${columnCount}, minmax(120px, 1fr))` }">
                <div v-for="(header, index) in headers" :key="`h${index}`" class="th">{{ header }}</div>
                <template v-for="(row, rowIndex) in rows" :key="rowIndex">
                    <div
                        v-for="cellIndex in columnCount"
                        :key="cellIndex"
                        :class="{ strong: cellIndex === 1 }"
                    >
                        <SmartLink
                            v-if="row.link && cellIndex === columnCount"
                            :to="row.link.url"
                            :new-tab="/^https?:/i.test(row.link.url)"
                            style="font-weight: 600"
                        >
                            {{ row.link.label }}
                        </SmartLink>
                        <template v-else>{{ row.cells[cellIndex - 1] ?? '' }}</template>
                    </div>
                </template>
            </div>
        </div>
        <p v-if="data.note" class="placeholder-note" style="margin: 12px 0 0">{{ data.note }}</p>
    </div>
</template>
