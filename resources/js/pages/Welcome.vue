<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppFooter from '@/components/AppFooter.vue';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="OWGG" />

    <main class="owgg-menu">
        <div class="owgg-grid" aria-hidden="true"></div>

        <header class="owgg-header">
            <div class="owgg-mark">
                <span class="owgg-mark-icon" aria-hidden="true"></span>
                <span>OWGG</span>
            </div>
            <span class="owgg-season">PERFORMANCE // SEASON 01</span>
        </header>

        <section class="owgg-content">
            <div class="owgg-copy">
                <p class="owgg-eyebrow">OVERWATCH // PERFORMANCE LAB</p>
                <h1>PLAY<br /><strong>SMARTER.</strong></h1>
                <p class="owgg-description">
                    Analysez vos parties, comprenez vos statistiques et
                    progressez avec Coach Winston.
                </p>

                <div class="owgg-actions">
                    <a href="/auth/battlenet" class="owgg-primary-action">
                        <span
                            class="owgg-action-icon"
                            aria-hidden="true"
                        ></span>
                        <span>
                            <small>CONNEXION RECOMMANDÉE</small>
                            SE CONNECTER AVEC BATTLE.NET
                        </span>
                        <span class="owgg-arrow">›</span>
                    </a>
                    <Link :href="login()" class="owgg-secondary-action">
                        Connexion classique
                    </Link>
                    <Link :href="register()" class="owgg-secondary-action">
                        Créer un compte
                    </Link>
                </div>
            </div>

            <div class="owgg-hero" aria-hidden="true">
                <div class="owgg-ring owgg-ring-outer"></div>
                <div class="owgg-ring owgg-ring-inner"></div>
                <div class="owgg-cross owgg-cross-horizontal"></div>
                <div class="owgg-cross owgg-cross-vertical"></div>
                <div class="owgg-core"></div>
            </div>
        </section>

        <footer class="owgg-footer">
            <span>INITIALIZING PLAYER PROFILE</span>
            <span class="owgg-status"><i></i> SYSTEM READY</span>
            <Link v-if="$page.props.auth.user" :href="dashboard()">
                Retourner au tableau de bord →
            </Link>
        </footer>
        <AppFooter />
    </main>
</template>

<style scoped>
.owgg-menu {
    --orange: var(--ow-orange);
    --blue: var(--role-tank);
    position: relative;
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    overflow: clip;
    background:
        radial-gradient(ellipse at 74% 44%, var(--bg-3) 0, transparent 40%),
        var(--bg-0);
    color: var(--text);
    font-family: var(--font-ui);
}

.owgg-grid {
    position: absolute;
    inset: 0;
    opacity: 0.045;
    background-image: repeating-linear-gradient(
        135deg,
        transparent 0 15px,
        var(--text) 15px 16px
    );
    pointer-events: none;
}

.owgg-header,
.owgg-footer,
.owgg-content {
    position: relative;
    z-index: 1;
    width: min(1180px, calc(100% - 48px));
    margin-inline: auto;
}

.owgg-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 32px;
    letter-spacing: 0.16em;
}

.owgg-mark {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-heading);
    font-size: 1.25rem;
    font-weight: 900;
    font-style: italic;
    letter-spacing: 0.1em;
}

.owgg-mark-icon {
    width: 18px;
    height: 18px;
    border: 2px solid var(--orange);
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
    background: color-mix(in srgb, var(--orange) 35%, transparent);
}

.owgg-season,
.owgg-eyebrow,
.owgg-footer {
    color: var(--text-dim);
    font-size: 0.68rem;
    letter-spacing: 0.18em;
}

.owgg-content {
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: space-between;
    gap: 48px;
    padding-block: 64px;
}

.owgg-copy {
    max-width: 560px;
}

.owgg-eyebrow {
    margin-bottom: 20px;
    color: var(--ow-orange-text);
    font-weight: 800;
}

h1 {
    margin: 0;
    font-size: clamp(4rem, 10vw, 8.5rem);
    font-weight: 900;
    letter-spacing: -0.08em;
    line-height: 0.78;
    font-style: italic;
    text-transform: uppercase;
}

h1 strong {
    color: var(--ow-orange-text);
}

.owgg-description {
    max-width: 390px;
    margin: 34px 0;
    color: var(--text-dim);
    font-size: 1rem;
    line-height: 1.6;
}

.owgg-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
}

.owgg-primary-action {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: min(100%, 430px);
    padding: 16px 20px;
    border: 0;
    position: relative;
    overflow: hidden;
    background: var(--orange);
    color: var(--text-on-accent);
    font-size: 0.9rem;
    font-weight: 900;
    letter-spacing: 0.05em;
    text-decoration: none;
    clip-path: polygon(
        0 0,
        calc(100% - 18px) 0,
        100% 50%,
        calc(100% - 18px) 100%,
        0 100%,
        8px 50%
    );
    transition:
        filter 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        box-shadow 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.owgg-primary-action::after {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        110deg,
        transparent 35%,
        var(--surface-highlight) 50%,
        transparent 65%
    );
    content: '';
    transform: translateX(-120%);
    transition: transform 220ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.owgg-primary-action:hover {
    box-shadow: 0 0 20px var(--ow-orange-glow);
    filter: brightness(1.04);
    transform: translateX(3px);
}

.owgg-primary-action:hover::after {
    transform: translateX(120%);
}

.owgg-primary-action:focus-visible,
.owgg-secondary-action:focus-visible {
    outline: 2px solid var(--ow-orange-bright);
    outline-offset: 4px;
}

.owgg-primary-action small {
    display: block;
    margin-bottom: 4px;
    font-size: 0.55rem;
    letter-spacing: 0.14em;
}

.owgg-action-icon {
    width: 22px;
    height: 22px;
    flex: 0 0 auto;
    border: 2px solid currentColor;
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
}

.owgg-arrow {
    margin-left: auto;
    font-size: 2rem;
    font-weight: 400;
}

.owgg-secondary-action {
    padding: 4px 18px;
    color: var(--text-dim);
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-decoration: none;
    text-transform: uppercase;
}

.owgg-secondary-action:hover,
.owgg-footer a:hover {
    color: var(--ow-orange-bright);
}

.owgg-hero {
    position: relative;
    width: min(43vw, 470px);
    aspect-ratio: 1;
    opacity: 0.7;
}

.owgg-ring {
    position: absolute;
    inset: 12%;
    border: 1px solid color-mix(in srgb, var(--role-tank) 35%, transparent);
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
}

.owgg-ring-outer {
    inset: 1%;
    border-color: color-mix(in srgb, var(--ow-orange) 75%, transparent);
    border-width: 2px;
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
    transform: rotate(45deg);
}

.owgg-ring-inner {
    inset: 25%;
    border-style: dashed;
    border-color: color-mix(in srgb, var(--role-tank) 60%, transparent);
    transform: rotate(-25deg);
}

.owgg-cross {
    position: absolute;
    top: 50%;
    left: 50%;
    background: color-mix(in srgb, var(--role-tank) 32%, transparent);
    transform: translate(-50%, -50%);
}

.owgg-cross-horizontal {
    width: 100%;
    height: 1px;
}

.owgg-cross-vertical {
    width: 1px;
    height: 100%;
}

.owgg-core {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 72px;
    height: 72px;
    border: 2px solid var(--orange);
    background: color-mix(in srgb, var(--orange) 15%, transparent);
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
    transform: translate(-50%, -50%);
}

.owgg-footer {
    display: flex;
    align-items: center;
    gap: 28px;
    padding-bottom: 28px;
    font-family: var(--font-heading);
}

.owgg-footer a {
    margin-left: auto;
    color: var(--text-dim);
    text-decoration: none;
}

.owgg-status {
    color: var(--win);
}

.owgg-status i {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 7px;
    background: var(--win);
}

@media (max-width: 700px) {
    .owgg-header,
    .owgg-footer,
    .owgg-content {
        width: min(100% - 32px, 560px);
    }

    .owgg-primary-action {
        clip-path: polygon(
            0 0,
            calc(100% - 12px) 0,
            100% 50%,
            calc(100% - 12px) 100%,
            0 100%,
            5px 50%
        );
    }

    .owgg-season {
        display: none;
    }

    .owgg-content {
        align-items: flex-start;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
    }

    .owgg-hero {
        position: absolute;
        top: 48%;
        right: -24%;
        width: 70vw;
        opacity: 0.3;
    }

    .owgg-copy {
        z-index: 1;
    }

    .owgg-footer {
        flex-wrap: wrap;
        gap: 12px;
    }

    .owgg-footer a {
        width: 100%;
        margin-left: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .owgg-primary-action,
    .owgg-primary-action::after {
        transition: none;
    }
}
</style>
