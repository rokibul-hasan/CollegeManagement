<script setup>
import { computed } from 'vue';
import ImageField from './ImageField.vue';

defineOptions({ name: 'SchemaForm' });

const props = defineProps({
    fields: { type: Array, required: true },
    model: { type: Object, required: true },
});

/**
 * Fields may declare showIf(model) to appear only for certain values of another field.
 */
const visibleFields = computed(() => props.fields.filter((field) => !field.showIf || field.showIf(props.model)));

function items(field) {
    if (!Array.isArray(props.model[field.key])) {
        props.model[field.key] = [];
    }

    return props.model[field.key];
}

function addItem(field) {
    items(field).push(JSON.parse(JSON.stringify(field.blank)));
}

function moveItem(field, index, direction) {
    const list = items(field);
    const target = index + direction;

    if (target >= 0 && target < list.length) {
        [list[index], list[target]] = [list[target], list[index]];
    }
}

function itemTitle(field, item, index) {
    return String(item[field.itemLabel] || '').slice(0, 60) || `#${index + 1}`;
}
</script>

<template>
    <div class="schema-form">
        <div v-for="field in visibleFields" :key="field.key" class="field" :class="{ full: ['textarea', 'code', 'repeater', 'image'].includes(field.type) }">
            <span class="label">{{ field.label }}</span>

            <input v-if="field.type === 'text'" v-model="model[field.key]" class="input">

            <textarea v-else-if="field.type === 'textarea'" v-model="model[field.key]" class="input" :rows="field.rows || 4" style="min-height: 0" />

            <textarea
                v-else-if="field.type === 'code'"
                v-model="model[field.key]"
                class="input mono code-input"
                rows="16"
                spellcheck="false"
                @keydown.tab.prevent="$event.target.setRangeText('  ', $event.target.selectionStart, $event.target.selectionEnd, 'end'); model[field.key] = $event.target.value"
            />

            <select v-else-if="field.type === 'select'" v-model="model[field.key]" class="input">
                <option v-for="[value, text] in field.options" :key="value" :value="value">{{ text }}</option>
            </select>

            <ImageField v-else-if="field.type === 'image'" v-model="model[field.key]" />

            <div v-else-if="field.type === 'repeater'" class="repeater">
                <details v-for="(item, index) in items(field)" :key="index" class="repeater-item" :open="!item[field.itemLabel]">
                    <summary>
                        <span class="repeater-title">{{ itemTitle(field, item, index) }}</span>
                        <span class="spacer" />
                        <button type="button" class="btn-icon" :disabled="index === 0" aria-label="উপরে" @click.prevent="moveItem(field, index, -1)">▲</button>
                        <button type="button" class="btn-icon" :disabled="index === items(field).length - 1" aria-label="নিচে" @click.prevent="moveItem(field, index, 1)">▼</button>
                        <button type="button" class="btn-icon" aria-label="মুছুন" style="color: var(--red)" @click.prevent="items(field).splice(index, 1)">✕</button>
                    </summary>
                    <SchemaForm :fields="field.fields" :model="item" />
                </details>
                <button type="button" class="btn btn-soft btn-sm" style="justify-self: start" @click="addItem(field)">{{ field.addLabel }}</button>
            </div>

            <span v-if="field.hint" class="hint">{{ field.hint }}</span>
        </div>
    </div>
</template>
