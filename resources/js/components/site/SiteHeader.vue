<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import SmartLink from './SmartLink.vue';
import { site } from '@/stores/site';
import { bnDigits } from '@/utils/bn';

const route = useRoute();
const navOpen = ref(false);
const openDropdown = ref(null);

watch(() => route.fullPath, () => {
    navOpen.value = false;
    openDropdown.value = null;
});

function pathOf(url) {
    return (url || '').split(/[?#]/)[0];
}

function isActive(item) {
    const path = pathOf(item.url);

    if (!path.startsWith('/')) {
        return false;
    }

    return path === '/' ? route.path === '/' : route.path === path || route.path.startsWith(`${path}/`);
}

function toggleDropdown(id) {
    openDropdown.value = openDropdown.value === id ? null : id;
}

function onParentClick(event, item) {
    if (!item.url) {
        event.preventDefault();
        toggleDropdown(item.id);
    }
}
</script>

<template>
    <header class="site-header">
        <div class="container brand-row">
            <router-link to="/" class="brand">
                <img :src="site.logo" alt="কলেজ লোগো">
                <div style="min-width: 0">
                    <h1>{{ site.settings.site_name }}</h1>
                    <div class="brand-sub">
                        {{ site.settings.site_name_en }}
                        <template v-if="site.settings.site_location">&nbsp;·&nbsp; {{ site.settings.site_location }}</template>
                    </div>
                </div>
            </router-link>
            <span class="spacer" />
            <div class="header-actions">
                <router-link to="/notices" class="btn btn-soft">
                    <span class="dot dot-blink" />
                    নোটিশ
                </router-link>
                <router-link to="/admission-fee" class="btn btn-primary">ভর্তি তথ্য</router-link>
                <button type="button" class="nav-toggle" :aria-expanded="navOpen" aria-label="মেনু" @click="navOpen = !navOpen">
                    {{ navOpen ? '×' : '☰' }}
                </button>
            </div>
        </div>
        <nav class="main-nav" :class="{ 'is-open': navOpen }">
            <div class="container">
                <template v-for="item in site.menus.main" :key="item.id">
                    <div
                        v-if="item.children.length"
                        class="nav-item has-children"
                        @mouseenter="openDropdown = item.id"
                        @mouseleave="openDropdown = null"
                    >
                        <SmartLink
                            :to="item.url || '#'"
                            :new-tab="item.newTab"
                            class="nav-link"
                            :class="{ 'is-active': isActive(item) }"
                            @click="onParentClick($event, item)"
                        >
                            {{ item.label }}
                            <span class="dot" style="width: 6px; height: 6px" />
                            <span class="nav-caret" @click.prevent.stop="toggleDropdown(item.id)">▾</span>
                        </SmartLink>
                        <div v-if="openDropdown === item.id" class="nav-dropdown">
                            <SmartLink v-for="child in item.children" :key="child.id" :to="child.url || '#'" :new-tab="child.newTab">
                                <span>{{ child.label }}</span>
                                <span v-if="child.count !== null" class="count">{{ bnDigits(String(child.count).padStart(2, '0')) }}</span>
                            </SmartLink>
                        </div>
                    </div>
                    <SmartLink
                        v-else
                        :to="item.url || '#'"
                        :new-tab="item.newTab"
                        class="nav-link"
                        :class="{ 'is-active': isActive(item) }"
                    >
                        {{ item.label }}
                    </SmartLink>
                </template>
            </div>
        </nav>
    </header>
</template>
