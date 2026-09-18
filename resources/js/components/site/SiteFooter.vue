<script setup>
import SmartLink from './SmartLink.vue';
import { site } from '@/stores/site';
</script>

<template>
    <footer class="site-footer">
        <div class="container cols">
            <div class="footer-brand">
                <div class="logo-row">
                    <img :src="site.logo" alt="কলেজ লোগো">
                    <div class="name">{{ site.settings.site_name }}</div>
                </div>
                <p v-if="site.settings.footer_about">{{ site.settings.footer_about }}</p>
                <p v-if="site.settings.address">{{ site.settings.address }}</p>
                <p v-if="site.settings.phone">{{ site.settings.phone }}</p>
                <p v-if="site.settings.email">{{ site.settings.email }}</p>
                <div v-if="site.settings.facebook_url || site.settings.youtube_url" class="socials">
                    <a v-if="site.settings.facebook_url" :href="site.settings.facebook_url" target="_blank" rel="noopener">Facebook</a>
                    <a v-if="site.settings.youtube_url" :href="site.settings.youtube_url" target="_blank" rel="noopener">YouTube</a>
                </div>
            </div>
            <div v-for="column in site.menus.footer" :key="column.id" class="footer-col">
                <div class="heading">{{ column.label }}</div>
                <div class="links">
                    <SmartLink v-for="link in column.children" :key="link.id" :to="link.url || '#'" :new-tab="link.newTab">
                        {{ link.label }}
                    </SmartLink>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <span>{{ site.settings.footer_copyright }}</span>
                <span class="spacer" />
                <span>
                    Developed by
                    <a href="https://xydigitalsolution.com/" target="_blank" rel="noopener" class="footer-credit">XY Digital Solution</a>
                </span>
            </div>
        </div>
    </footer>
</template>
