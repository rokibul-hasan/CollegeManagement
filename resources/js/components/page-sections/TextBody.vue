<script setup>
import { computed } from 'vue';
import { textBlocks } from '@/utils/html';

const props = defineProps({
    text: { type: String, default: '' },
});

const blocks = computed(() => textBlocks(props.text));
</script>

<template>
    <div class="ps-body">
        <template v-for="(block, index) in blocks" :key="index">
            <ul v-if="block.type === 'list'">
                <li v-for="(item, itemIndex) in block.items" :key="itemIndex">{{ item }}</li>
            </ul>
            <p v-else>{{ block.text }}</p>
        </template>
    </div>
</template>
