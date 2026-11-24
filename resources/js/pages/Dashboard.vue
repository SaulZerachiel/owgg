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

        <section class="dashboard-content">
            <header class="dashboard-header">
                <div>
                    <p class="dashboard-eyebrow">
                        OVERWATCH // PERFORMANCE LAB
                    </p>
                    <h1>PLAYER <strong>PROFILE</strong></h1>
                </div>
                <span class="dashboard-status"><i></i> SYSTEM ONLINE</span>
            </header>

            <section class="profile-panel">
                <div class="profile-mark" aria-hidden="true"></div>
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
                    <span
                        >Coach Winston est prêt à analyser vos
                        performances</span
                    >
                </article>
            </section>

            <section class="coming-soon">
                <div class="coming-soon-icon" aria-hidden="true"></div>
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
    --orange: var(--ow-orange);
    --blue: var(--role-tank);
    position: relative;
    height: calc(100dvh - 76px);
    min-height: 0;
    overflow: hidden;
    background:
        radial-gradient(ellipse at 82% 18%, var(--bg-3) 0, transparent 48%),
        var(--bg-0);
    color: var(--text);
    font-family: var(--font-ui);
}

.dashboard-grid {
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
    animation: dashboard-enter 320ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
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
    color: var(--text-dim);
    font-size: 0.68rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
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
    font-style: italic;
    text-transform: uppercase;
}

h1 strong {
    color: var(--ow-orange-bright);
}

.dashboard-status {
    color: var(--win);
    white-space: nowrap;
    font-family: var(--font-heading);
    font-weight: 800;
}

.dashboard-status i {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 7px;
    background: var(--win);
}

.profile-panel,
.stat-card,
.coming-soon {
    position: relative;
    border: 1px solid var(--surface-line-strong);
    background: var(--bg-2);
    clip-path: polygon(
        0 0,
        calc(100% - 18px) 0,
        100% 18px,
        100% 100%,
        18px 100%,
        0 calc(100% - 18px)
    );
}

.profile-panel::before,
.stat-card::before,
.coming-soon::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 32px;
    background: var(--ow-orange);
    content: '';
}

.profile-panel {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: clamp(18px, 2.5vh, 26px) 30px;
    border-left: 0;
}

.profile-mark {
    width: 42px;
    height: 42px;
    flex: 0 0 auto;
    border: 2px solid var(--ow-orange);
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
    background: color-mix(in srgb, var(--ow-orange) 18%, transparent);
}

.profile-panel h2,
.coming-soon h2 {
    margin-bottom: 8px;
    font-size: clamp(1.4rem, 3vw, 2.2rem);
    letter-spacing: 0.04em;
    font-style: italic;
    text-transform: uppercase;
}

.panel-label {
    color: var(--orange);
    font-family: var(--font-heading);
    font-weight: 800;
}

.panel-copy,
.coming-soon p,
.stat-card span {
    margin-bottom: 0;
    color: var(--text-dim);
    font-size: 0.9rem;
    line-height: 1.5;
}

.connected-badge {
    margin-left: auto;
    padding: 7px 10px;
    color: var(--win);
    border: 1px solid color-mix(in srgb, var(--win) 42%, transparent);
    font-size: 0.62rem;
    letter-spacing: 0.12em;
    font-family: var(--font-heading);
    font-weight: 800;
    clip-path: polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 0 100%);
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
    transition:
        border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.stat-card:hover {
    border-color: color-mix(in srgb, var(--ow-orange) 65%, transparent);
    transform: translateY(-2px);
}

.stat-card strong {
    display: block;
    margin: 12px 0 8px;
    color: var(--text);
    font-size: 2rem;
    letter-spacing: 0.06em;
    font-variant-numeric: tabular-nums;
    font-family: var(--font-heading);
}

.stat-card-accent {
    border-color: color-mix(in srgb, var(--ow-orange) 48%, transparent);
}

.stat-card-accent strong {
    color: var(--ow-orange-bright);
    font-size: 1.35rem;
}

.coming-soon {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: clamp(18px, 2.5vh, 26px) 30px;
}

.coming-soon-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 auto;
    border: 2px solid var(--orange);
    background: color-mix(in srgb, var(--orange) 12%, transparent);
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
    color: var(--orange);
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

@media (prefers-reduced-motion: reduce) {
    .dashboard-header,
    .profile-panel,
    .dashboard-cards,
    .coming-soon {
        animation: none;
    }

    .stat-card {
        transition: none;
    }

    .stat-card:hover {
        transform: none;
    }
}
</style>
