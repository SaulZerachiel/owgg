<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppFooter from '@/components/AppFooter.vue';
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
        <AppFooter />
    </div>
</template>

<style scoped>
.owgg-auth-shell {
    position: relative;
    display: flex;
    min-height: 100svh;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    overflow-x: hidden;
    overflow-y: auto;
    isolation: isolate;
    padding: 28px 18px;
    background:
        radial-gradient(ellipse at 82% 14%, var(--bg-3) 0, transparent 44%),
        var(--bg-0);
    color: var(--text);
}

.owgg-auth-shell::before {
    position: absolute;
    inset: 0;
    z-index: 0;
    background-image: repeating-linear-gradient(
        135deg,
        transparent 0 16px,
        var(--surface-line) 16px 17px
    );
    content: '';
    pointer-events: none;
}

.auth-grid {
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 0;
    width: min(74vw, 600px);
    aspect-ratio: 1;
    border: 1px solid var(--surface-line);
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
    pointer-events: none;
    transform: translate(-50%, -50%);
}

.auth-card {
    position: relative;
    z-index: 1;
    width: min(100%, 430px);
    padding: clamp(24px, 6vw, 38px);
    border: 1px solid var(--surface-line-strong);
    border-top: 3px solid var(--ow-orange);
    background: var(--bg-2);
    clip-path: polygon(
        0 0,
        calc(100% - 22px) 0,
        100% 22px,
        100% 100%,
        16px 100%,
        0 calc(100% - 16px)
    );
}

.owgg-auth-shell :deep(.owgg-legal-footer) {
    width: min(100%, 430px);
    padding: 10px 4px;
    border-top: 0;
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
    color: var(--ow-orange-text);
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
    text-transform: uppercase;
    font-style: italic;
}

.auth-heading > p:last-child {
    margin: 0;
    color: var(--text-dim);
    font-size: 0.9rem;
    line-height: 1.55;
}

.auth-footer {
    margin: 26px 0 0;
    padding-top: 14px;
    border-top: 1px solid var(--surface-line);
    color: var(--text-mute);
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
