<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="OWGG" />

    <main class="owgg-menu">
        <div class="owgg-grid" aria-hidden="true"></div>
        <div class="owgg-glow owgg-glow-left" aria-hidden="true"></div>
        <div class="owgg-glow owgg-glow-right" aria-hidden="true"></div>

        <header class="owgg-header">
            <div class="owgg-mark">
                <span class="owgg-mark-icon">✦</span>
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
                        <span class="owgg-action-icon">◈</span>
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
                <div class="owgg-core">✦</div>
            </div>
        </section>

        <footer class="owgg-footer">
            <span>INITIALIZING PLAYER PROFILE</span>
            <span class="owgg-status"><i></i> SYSTEM READY</span>
            <Link v-if="$page.props.auth.user" :href="dashboard()">
                Retourner au tableau de bord →
            </Link>
        </footer>
    </main>
</template>

<style scoped>
.owgg-menu {
    --orange: #f06432;
    --blue: #a9e8ff;
    position: relative;
    display: flex;
    min-height: 100vh;
    flex-direction: column;
    overflow: hidden;
    background:
        radial-gradient(circle at 72% 48%, #18384b 0, #0d1b27 22%, transparent 48%),
        linear-gradient(125deg, #091019 0%, #111d29 58%, #0a1119 100%);
    color: #eef6f8;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

.owgg-grid {
    position: absolute;
    inset: 0;
    opacity: 0.18;
    background-image:
        linear-gradient(#8fd9e915 1px, transparent 1px),
        linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 46px 46px;
    transform: perspective(500px) rotateX(55deg) scale(1.5) translateY(28%);
}

.owgg-glow {
    position: absolute;
    width: 40vw;
    height: 40vw;
    border-radius: 999px;
    filter: blur(80px);
    opacity: 0.16;
}

.owgg-glow-left {
    top: 20%;
    left: -20%;
    background: var(--orange);
}

.owgg-glow-right {
    right: -15%;
    bottom: -25%;
    background: var(--blue);
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
    font-size: 1.15rem;
    font-weight: 900;
}

.owgg-mark-icon {
    color: var(--orange);
    font-size: 1.8rem;
    line-height: 1;
}

.owgg-season,
.owgg-eyebrow,
.owgg-footer {
    color: #88a6b2;
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
    color: var(--orange);
}

h1 {
    margin: 0;
    font-size: clamp(4rem, 10vw, 8.5rem);
    font-weight: 900;
    letter-spacing: -0.08em;
    line-height: 0.78;
}

h1 strong {
    color: transparent;
    -webkit-text-stroke: 2px var(--blue);
}

.owgg-description {
    max-width: 390px;
    margin: 34px 0;
    color: #b6c8ce;
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
    background: var(--orange);
    color: #17171a;
    font-size: 0.9rem;
    font-weight: 900;
    letter-spacing: 0.05em;
    text-decoration: none;
    clip-path: polygon(0 0, 94% 0, 100% 50%, 94% 100%, 0 100%, 3% 50%);
    transition: filter 0.2s, transform 0.2s;
}

.owgg-primary-action:hover {
    filter: brightness(1.15);
    transform: translateX(5px);
}

.owgg-primary-action small {
    display: block;
    margin-bottom: 4px;
    font-size: 0.55rem;
    letter-spacing: 0.14em;
}

.owgg-action-icon {
    font-size: 1.6rem;
}

.owgg-arrow {
    margin-left: auto;
    font-size: 2rem;
    font-weight: 400;
}

.owgg-secondary-action {
    padding: 4px 18px;
    color: #a7bec7;
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-decoration: none;
    text-transform: uppercase;
}

.owgg-secondary-action:hover,
.owgg-footer a:hover {
    color: var(--blue);
}

.owgg-hero {
    position: relative;
    width: min(43vw, 470px);
    aspect-ratio: 1;
    opacity: 0.88;
}

.owgg-ring {
    position: absolute;
    inset: 12%;
    border: 1px solid #a9e8ff66;
    border-radius: 50%;
    box-shadow: 0 0 35px #8bdffc22, inset 0 0 35px #8bdffc11;
}

.owgg-ring-outer {
    inset: 1%;
    border-color: #f0643266;
    border-width: 2px;
    border-left-color: transparent;
    border-bottom-color: transparent;
    transform: rotate(35deg);
}

.owgg-ring-inner {
    inset: 25%;
    border-style: dashed;
    border-color: #a9e8ff88;
    transform: rotate(-25deg);
}

.owgg-cross {
    position: absolute;
    top: 50%;
    left: 50%;
    background: #a9e8ff44;
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
    display: grid;
    width: 100px;
    height: 100px;
    place-items: center;
    border: 2px solid var(--orange);
    color: var(--orange);
    font-size: 3.2rem;
    transform: translate(-50%, -50%) rotate(45deg);
    box-shadow: 0 0 35px #f0643266;
}

.owgg-core::first-letter {
    transform: rotate(-45deg);
}

.owgg-footer {
    display: flex;
    align-items: center;
    gap: 28px;
    padding-bottom: 28px;
}

.owgg-footer a {
    margin-left: auto;
    color: #a7bec7;
    text-decoration: none;
}

.owgg-status {
    color: #7fd8ae;
}

.owgg-status i {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 7px;
    border-radius: 50%;
    background: #7fd8ae;
    box-shadow: 0 0 10px #7fd8ae;
}

@media (max-width: 700px) {
    .owgg-header,
    .owgg-footer,
    .owgg-content {
        width: min(100% - 32px, 560px);
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
</style>
