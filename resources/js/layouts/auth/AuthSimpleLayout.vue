<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="owgg-auth-shell dark">
        <div class="auth-grid" aria-hidden="true"></div>
        <main class="auth-card">
            <Link :href="home()" class="auth-brand">
                <AppLogo />
            </Link>
            <div class="auth-heading">
                <p class="auth-eyebrow">ACCESS TERMINAL // OWGG</p>
                <h1>{{ title }}</h1>
                <p>{{ description }}</p>
            </div>
            <slot />
            <p class="auth-footer">PLAYER PERFORMANCE // SECURE ACCESS</p>
        </main>
    </div>
</template>

<style scoped>
.owgg-auth-shell {
    position: relative;
    display: grid;
    min-height: 100svh;
    place-items: center;
    overflow: hidden;
    isolation: isolate;
    padding: 28px 18px;
    background:
        radial-gradient(circle at 82% 18%, #174056 0, transparent 34%),
        radial-gradient(circle at 12% 90%, #f0643214 0, transparent 30%),
        linear-gradient(135deg, #091019, #111d29 58%, #0a1119);
    color: #eef6f8;
}

.owgg-auth-shell::before {
    position: absolute;
    inset: 0;
    z-index: 0;
    background-image:
        linear-gradient(#8fd9e915 1px, transparent 1px),
        linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 44px 44px;
    content: '';
    opacity: 0.28;
    pointer-events: none;
}

.auth-grid {
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 0;
    width: min(80vw, 680px);
    aspect-ratio: 1;
    border: 1px solid #a9e8ff12;
    border-radius: 50%;
    box-shadow:
        0 0 0 48px #a9e8ff08,
        0 0 0 96px #a9e8ff05;
    pointer-events: none;
    transform: translate(-50%, -50%);
}

.auth-card {
    position: relative;
    z-index: 1;
    width: min(100%, 430px);
    padding: clamp(24px, 6vw, 38px);
    border: 1px solid #a9e8ff38;
    border-top: 2px solid #f06432;
    background: linear-gradient(145deg, #10202df2, #09131df5);
    box-shadow: 0 24px 80px #03080db3;
}

.auth-brand {
    display: flex;
    width: fit-content;
    align-items: center;
    margin-bottom: 32px;
    text-decoration: none;
}

.auth-heading {
    margin-bottom: 26px;
}

.auth-eyebrow,
.auth-footer {
    color: #f06432;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.16em;
}

.auth-eyebrow {
    margin: 0 0 8px;
}

.auth-heading h1 {
    margin: 0 0 8px;
    font-size: clamp(1.45rem, 5vw, 1.8rem);
    font-weight: 850;
    letter-spacing: -0.035em;
}

.auth-heading > p:last-child {
    margin: 0;
    color: #9ab2bd;
    font-size: 0.9rem;
    line-height: 1.55;
}

.auth-footer {
    margin: 26px 0 0;
    padding-top: 14px;
    border-top: 1px solid #a9e8ff20;
    color: #78909c;
    font-size: 0.56rem;
}

@media (max-width: 420px) {
    .auth-card {
        padding: 24px 20px;
    }

    .auth-brand {
        margin-bottom: 24px;
    }
}
</style>
