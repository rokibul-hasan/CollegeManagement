<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import NoticeBoard from '@/components/sections/NoticeBoard.vue';
import StreamsGrid from '@/components/sections/StreamsGrid.vue';
import FacultyGrid from '@/components/sections/FacultyGrid.vue';
import RoutineTable from '@/components/sections/RoutineTable.vue';
import FeeCard from '@/components/sections/FeeCard.vue';
import ResultCard from '@/components/sections/ResultCard.vue';
import StudentLoginCard from '@/components/sections/StudentLoginCard.vue';
import CoCurricularGrid from '@/components/sections/CoCurricularGrid.vue';
import GalleryGrid from '@/components/sections/GalleryGrid.vue';
import AdmissionBand from '@/components/sections/AdmissionBand.vue';
import QuickLinksCard from '@/components/sections/QuickLinksCard.vue';
import { about, admissionFees, formFees, hero, messages, services } from '@/data/content';
import { site } from '@/stores/site';

const slide = ref(0);
let timer = null;

function startAutoplay() {
    clearInterval(timer);
    timer = setInterval(() => {
        slide.value = (slide.value + 1) % hero.slides.length;
    }, 6000);
}

function goTo(index) {
    slide.value = index;
    startAutoplay();
}

onMounted(startAutoplay);
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <section class="hero-wrap">
        <div class="container">
            <div class="hero">
                <img
                    v-for="(item, index) in hero.slides"
                    v-show="slide === index"
                    :key="index"
                    :src="item.image"
                    :alt="item.alt"
                    :style="{ objectPosition: item.position }"
                >
                <div class="hero-shade" />
                <div class="hero-body">
                    <div class="hero-badge">{{ hero.badge }}</div>
                    <h2>{{ site.settings.site_name }}</h2>
                    <p>{{ hero.lead }}</p>
                    <div class="hero-actions">
                        <router-link to="/admission-fee" class="btn btn-amber">ভর্তি আবেদন</router-link>
                        <router-link to="/departments" class="btn btn-ghost-light">বিভাগসমূহ</router-link>
                    </div>
                </div>
                <div class="hero-dots">
                    <button
                        v-for="(item, index) in hero.slides"
                        :key="index"
                        type="button"
                        :class="{ 'is-active': slide === index }"
                        :aria-label="`স্লাইড ${index + 1}`"
                        @click="goTo(index)"
                    />
                </div>
            </div>

            <NoticeBoard />
        </div>
    </section>

    <div class="container" style="padding-top: 44px; padding-bottom: 44px">
        <div class="intro-grid">
            <div style="display: grid; gap: 30px">
                <div id="itihas" class="card intro-card">
                    <h3>প্রতিষ্ঠানের পরিচিতি</h3>
                    <img :src="about.image" alt="কলেজ ভবন">
                    <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.9; color: var(--text); text-wrap: pretty">
                        {{ about.summary }}
                        <span class="placeholder-note">{{ about.placeholderNote }}</span>
                    </p>
                    <router-link to="/about" style="font-size: 15px; font-weight: 600">বিস্তারিত →</router-link>
                </div>

                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px">
                    <div v-for="message in messages" :key="message.title" class="message-card">
                        <div class="portrait"><div class="avatar">{{ message.initial }}</div></div>
                        <div style="min-width: 0">
                            <h4>{{ message.title }}</h4>
                            <p>{{ message.excerpt }}</p>
                            <router-link to="/about#messages" style="font-size: 14px; font-weight: 600">বিস্তারিত →</router-link>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="sidebar">
                <QuickLinksCard />
                <div class="card-deep">
                    <div style="font-size: 12.5px; letter-spacing: .14em; color: var(--gold-light); margin-bottom: 10px">ফলাফল</div>
                    <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.75">রোল ও রেজিস্ট্রেশন নম্বর দিয়ে মার্কশিট দেখুন।</p>
                    <router-link to="/result" class="btn btn-gold btn-block">ফলাফল দেখুন</router-link>
                </div>
            </aside>
        </div>
    </div>

    <section class="section-white">
        <div class="container" style="padding-top: 46px; padding-bottom: 46px">
            <h3 class="serif-title">দ্রুত সেবা</h3>
            <div class="grid grid-auto-210">
                <router-link v-for="service in services" :key="service.no" :to="service.to" class="service-card">
                    <div class="no">{{ service.no }}</div>
                    <div class="title">{{ service.title }}</div>
                    <div class="sub">{{ service.sub }}</div>
                </router-link>
            </div>
        </div>
    </section>

    <section id="bibhag" class="container section">
        <h3 class="serif-title" style="margin-bottom: 6px">শিক্ষা কার্যক্রম</h3>
        <p style="margin: 0 0 26px; font-size: 15.5px; color: var(--muted)">উচ্চ মাধ্যমিক পর্যায়ে তিনটি শাখা</p>
        <StreamsGrid />
    </section>

    <AdmissionBand />

    <section id="shikkhok" class="container section">
        <div class="section-head">
            <h3 class="serif-title">শিক্ষকমন্ডলী</h3>
            <span class="spacer" />
            <router-link to="/teachers" class="more-link">সকল শিক্ষক ও কর্মচারী →</router-link>
        </div>
        <FacultyGrid :limit="4" />
    </section>

    <section id="routine" class="container" style="padding-top: 50px; padding-bottom: 10px">
        <h3 class="bold-title">ক্লাস রুটিন</h3>
        <RoutineTable />
    </section>

    <section id="form-fee" class="container section-tight">
        <div class="grid grid-auto-300">
            <FeeCard title="এডমিশন ফি" :subtitle="admissionFees.subtitle" :rows="admissionFees.rows" :note="admissionFees.note" />
            <FeeCard
                title="ফরম পূরণ ফি"
                :subtitle="formFees.subtitle"
                :rows="formFees.rows"
                :link="{ label: 'বিজ্ঞপ্তি ডাউনলোড', to: '/notices?category=exam' }"
            />
        </div>
    </section>

    <section class="container section-tight">
        <div class="grid grid-auto-300">
            <ResultCard />
            <StudentLoginCard />
        </div>
    </section>

    <section id="cocurricular" class="container section-tight">
        <h3 class="bold-title" style="margin-bottom: 6px">সহশিক্ষা কার্যক্রম</h3>
        <p style="margin: 0 0 24px; font-size: 15.5px; color: var(--muted)">পাঠ্যক্রমের বাইরে শিক্ষার্থীদের অংশগ্রহণের ক্ষেত্র</p>
        <CoCurricularGrid />
    </section>

    <section id="gallery" style="background: #fff; border-top: 1px solid var(--line); margin-top: 44px">
        <div class="container" style="padding-top: 48px; padding-bottom: 48px">
            <h3 class="serif-title">ক্যাম্পাসের মুহূর্ত</h3>
            <GalleryGrid />
        </div>
    </section>
</template>
