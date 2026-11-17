<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage<{
    auth: { user: { name: string; battletag?: string } };
}>();
const user = page.props.auth.user;
</script>

<template>
    <Head title="Dashboard" />

    <main class="owgg-dashboard">
        <div class="dashboard-grid" aria-hidden="true"></div>
        <div class="dashboard-glow dashboard-glow-left" aria-hidden="true"></div>
        <div class="dashboard-glow dashboard-glow-right" aria-hidden="true"></div>

        <section class="dashboard-content">
            <header class="dashboard-header">
                <div>
                    <p class="dashboard-eyebrow">OVERWATCH // PERFORMANCE LAB</p>
                    <h1>PLAYER <strong>PROFILE</strong></h1>
                </div>
                <span class="dashboard-status"><i></i> SYSTEM ONLINE</span>
            </header>

            <section class="profile-panel">
                <div class="profile-mark">✦</div>
                <div>
                    <p class="panel-label">BATTLE.NET ID</p>
                    <h2>{{ user.battletag ?? user.name }}</h2>
                    <p class="panel-copy">
                        Profil connecté. Les statistiques Overwatch apparaîtront
                        ici dès que la connexion OverFast sera activée.
                    </p>
                </div>
                <span class="connected-badge">CONNECTED</span>
            </section>

            <section class="dashboard-cards">
                <article class="stat-card">
                    <p class="panel-label">MATCH HISTORY</p>
                    <strong>—</strong>
                    <span>En attente des données de parties</span>
                </article>
                <article class="stat-card">
                    <p class="panel-label">PERFORMANCE SCORE</p>
                    <strong>—</strong>
                    <span>Analyse bientôt disponible</span>
                </article>
                <article class="stat-card stat-card-accent">
                    <p class="panel-label">COACH WINSTON</p>
                    <strong>READY</strong>
                    <span>Coach Winston est prêt à analyser vos performances</span>
                </article>
            </section>

            <section class="coming-soon">
                <div class="coming-soon-icon">◈</div>
                <div>
                    <p class="panel-label">DATA LINK // OVERFAST</p>
                    <h2>ANALYSE YOUR GAME.</h2>
                    <p>
                        Synchronisation des héros, matchs et statistiques en
                        préparation.
                    </p>
                </div>
            </section>
        </section>
    </main>
</template>

<style scoped>
.owgg-dashboard {
    --orange: #f06432;
    --blue: #a9e8ff;
    position: relative;
    height: calc(100dvh - 76px);
    min-height: 0;
    overflow: hidden;
    background:
        radial-gradient(circle at 78% 25%, #18384b 0, #0d1b27 22%, transparent 52%),
        linear-gradient(125deg, #091019 0%, #111d29 58%, #0a1119 100%);
    color: #eef6f8;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

.dashboard-grid {
    position: absolute;
    inset: 0;
    opacity: 0.16;
    background-image:
        linear-gradient(#8fd9e915 1px, transparent 1px),
        linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 46px 46px;
    transform: perspective(500px) rotateX(55deg) scale(1.5) translateY(28%);
}

.dashboard-glow {
    position: absolute;
    width: 32vw;
    height: 32vw;
    border-radius: 999px;
    filter: blur(80px);
    opacity: 0.14;
}

.dashboard-glow-left {
    top: 20%;
    left: -20%;
    background: var(--orange);
}

.dashboard-glow-right {
    right: -15%;
    bottom: -25%;
    background: var(--blue);
}

.dashboard-content {
    position: relative;
    z-index: 1;
    width: min(1440px, calc(100% - 12vw));
    height: 100%;
    margin-inline: auto;
    padding: clamp(24px, 4vh, 42px) 0;
}

.dashboard-header,
.profile-panel,
.dashboard-cards,
.coming-soon {
    animation: dashboard-enter 0.45s ease-out both;
}

.dashboard-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: clamp(20px, 3vh, 30px);
}

.dashboard-eyebrow,
.panel-label,
.dashboard-status {
    color: #88a6b2;
    font-size: 0.68rem;
    letter-spacing: 0.18em;
}

.dashboard-eyebrow,
.panel-label {
    margin: 0 0 10px;
}

.dashboard-eyebrow {
    color: var(--orange);
}

h1,
h2,
p {
    margin-top: 0;
}

h1 {
    margin-bottom: 0;
    font-size: clamp(2.2rem, 5vw, 4.5rem);
    font-weight: 900;
    letter-spacing: -0.06em;
    line-height: 0.9;
}

h1 strong {
    color: transparent;
    -webkit-text-stroke: 1px var(--blue);
}

.dashboard-status {
    color: #7fd8ae;
    white-space: nowrap;
}

.dashboard-status i {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 7px;
    border-radius: 50%;
    background: #7fd8ae;
    box-shadow: 0 0 10px #7fd8ae;
}

.profile-panel,
.stat-card,
.coming-soon {
    border: 1px solid #a9e8ff2b;
    background: #09151f99;
    box-shadow: 0 0 30px #07101866;
}

.profile-panel {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: clamp(18px, 2.5vh, 26px) 30px;
    border-left: 3px solid var(--orange);
}

.profile-mark {
    display: grid;
    width: 58px;
    height: 58px;
    flex: 0 0 auto;
    place-items: center;
    border: 1px solid var(--orange);
    color: var(--orange);
    font-size: 2rem;
    transform: rotate(45deg);
}

.profile-mark::first-letter {
    transform: rotate(-45deg);
}

.profile-panel h2,
.coming-soon h2 {
    margin-bottom: 8px;
    font-size: clamp(1.4rem, 3vw, 2.2rem);
    letter-spacing: 0.04em;
}

.panel-label {
    color: var(--orange);
}

.panel-copy,
.coming-soon p,
.stat-card span {
    margin-bottom: 0;
    color: #a7bec7;
    font-size: 0.9rem;
    line-height: 1.5;
}

.connected-badge {
    margin-left: auto;
    padding: 7px 10px;
    color: #7fd8ae;
    border: 1px solid #7fd8ae66;
    font-size: 0.62rem;
    letter-spacing: 0.12em;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin: 16px 0;
}

.stat-card {
    min-height: 130px;
    padding: 22px;
}

.stat-card strong {
    display: block;
    margin: 12px 0 8px;
    color: var(--blue);
    font-size: 2rem;
    letter-spacing: 0.06em;
}

.stat-card-accent {
    border-color: #f0643266;
}

.stat-card-accent strong {
    color: var(--orange);
    font-size: 1.35rem;
}

.coming-soon {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: clamp(18px, 2.5vh, 26px) 30px;
}

.coming-soon-icon {
    color: var(--orange);
    font-size: 2.4rem;
}

@keyframes dashboard-enter {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 700px) {
    .owgg-dashboard {
        height: auto;
        min-height: calc(100dvh - 64px);
        overflow: auto;
    }

    .dashboard-content {
        width: min(100% - 28px, 1120px);
        height: auto;
        padding: 28px 0;
    }

    .dashboard-header,
    .profile-panel,
    .coming-soon {
        align-items: flex-start;
        flex-direction: column;
    }

    .connected-badge {
        margin-left: 0;
    }

    .dashboard-cards {
        grid-template-columns: 1fr;
    }
}
</style>
