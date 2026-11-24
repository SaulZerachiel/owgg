<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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

type DashboardProfile = {
    name: string;
    avatar: string | null;
    title: string | null;
};

type Summary = {
    games_played: number | string | null;
    games_won: number | string | null;
    games_lost: number | string | null;
    winrate: number | string | null;
    kda: number | string | null;
    time_played: number | string | null;
    deaths_avg_per_10_min: number | string | null;
};

type RoleRank = {
    role: string;
    season: string;
    tier: string | null;
    division: number | null;
};

type RoleStats = {
    role: string;
    games_played: number | string | null;
    winrate: number | string | null;
    kda: number | string | null;
};

type RoleCard = {
    role: string;
    season: string | null;
    tier: string | null;
    division: number | null;
    games_played: number | string | null;
    winrate: number | string | null;
    kda: number | string | null;
};

type Hero = {
    hero: string;
    role: string;
    games_played: number | string | null;
    winrate: number | string | null;
    time_played: number | string | null;
};

type Comparison = {
    current: number | null;
    previous: number | null;
    change: number | null;
};

type Progression = {
    capturedAt: string;
    previousCapturedAt: string;
    winrate: Comparison;
    deathsPer10: Comparison;
};

const props = defineProps<{
    profile: DashboardProfile | null;
    summary: Summary | null;
    ranks: RoleRank[];
    roles: RoleStats[];
    topHeroes: Hero[];
    progression: Progression | null;
    statsSyncedAt: string | null;
    statisticsUrl: string | null;
    coachUrl: string;
}>();

const page = usePage<{
    auth: { user: { name: string; battletag?: string | null } };
}>();
const user = page.props.auth.user;
const roleCards = computed<RoleCard[]>(() => {
    const cards: RoleCard[] = props.roles.map((role) => {
        const rank = props.ranks.find((item) => item.role === role.role);

        return {
            role: role.role,
            season: rank?.season ?? null,
            tier: rank?.tier ?? null,
            division: rank?.division ?? null,
            games_played: role.games_played,
            winrate: role.winrate,
            kda: role.kda,
        };
    });

    props.ranks.forEach((rank) => {
        if (!cards.some((role) => role.role === rank.role)) {
            cards.push({
                ...rank,
                games_played: null,
                winrate: null,
                kda: null,
            });
        }
    });

    return cards;
});

function formatNumber(value: number | string | null, digits = 1): string {
    if (value === null || value === '') {
        return '—';
    }

    const number = Number(value);

    return Number.isFinite(number)
        ? new Intl.NumberFormat(undefined, {
              maximumFractionDigits: digits,
          }).format(number)
        : '—';
}

function formatDuration(value: number | string | null): string {
    const seconds = Number(value);

    if (!Number.isFinite(seconds)) {
        return '—';
    }

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);

    return hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
}

function formatDate(value: string | null): string {
    if (!value) {
        return 'Not synced yet';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function formatChange(value: number | null, suffix: string): string {
    if (value === null) {
        return 'No comparison yet';
    }

    const sign = value > 0 ? '+' : '';

    return `${sign}${formatNumber(value)} ${suffix}`;
}

function changeClass(value: number | null, lowerIsBetter = false): string {
    if (value === null || value === 0) {
        return 'trend-neutral';
    }

    return (lowerIsBetter ? value < 0 : value > 0)
        ? 'trend-positive'
        : 'trend-negative';
}
</script>

<template>
    <Head title="Dashboard" />

    <main class="owgg-dashboard">
        <div class="dashboard-grid" aria-hidden="true"></div>

        <section class="dashboard-content">
            <header class="dashboard-header">
                <div>
                    <p class="dashboard-eyebrow">OVERWATCH // COMMAND CENTER</p>
                    <h1>PLAYER <strong>OVERVIEW</strong></h1>
                </div>
                <p class="dashboard-status">
                    LAST SYNC · {{ formatDate(statsSyncedAt) }}
                </p>
            </header>

            <section class="profile-panel">
                <img
                    v-if="profile?.avatar"
                    :src="profile.avatar"
                    :alt="`${profile.name} avatar`"
                    class="profile-avatar"
                />
                <div v-else class="profile-mark" aria-hidden="true"></div>
                <div>
                    <p class="panel-label">BATTLE.NET ID</p>
                    <h2>{{ profile?.name ?? user.battletag ?? user.name }}</h2>
                    <p v-if="profile?.title" class="panel-copy">
                        {{ profile.title }}
                    </p>
                </div>
                <a
                    v-if="statisticsUrl"
                    class="secondary-button"
                    :href="statisticsUrl"
                >
                    Full statistics
                </a>
            </section>

            <section v-if="summary" class="dashboard-cards">
                <article class="stat-card stat-card-accent">
                    <p class="panel-label">WIN RATE</p>
                    <strong>{{ formatNumber(summary.winrate) }}%</strong>
                    <span>
                        {{ formatNumber(summary.games_won, 0) }} wins /
                        {{ formatNumber(summary.games_played, 0) }} games
                    </span>
                </article>
                <article class="stat-card">
                    <p class="panel-label">COMPETITIVE GAMES</p>
                    <strong>{{ formatNumber(summary.games_played, 0) }}</strong>
                    <span>
                        {{ formatNumber(summary.games_won, 0) }} wins ·
                        {{ formatNumber(summary.games_lost, 0) }} losses
                    </span>
                </article>
                <article class="stat-card">
                    <p class="panel-label">KDA</p>
                    <strong>{{ formatNumber(summary.kda) }}</strong>
                    <span>Career average</span>
                </article>
                <article class="stat-card">
                    <p class="panel-label">DEATHS / 10 MIN</p>
                    <strong>
                        {{ formatNumber(summary.deaths_avg_per_10_min) }}
                    </strong>
                    <span>Career average</span>
                </article>
                <article class="stat-card">
                    <p class="panel-label">TIME PLAYED</p>
                    <strong>{{ formatDuration(summary.time_played) }}</strong>
                    <span>Competitive, all heroes</span>
                </article>
            </section>
            <section v-else class="empty-panel">
                <div>
                    <p class="panel-label">NO SYNCED STATISTICS</p>
                    <h2>Your overview is waiting for data.</h2>
                    <p>
                        Open the statistics page to start or refresh your
                        OverFast import.
                    </p>
                </div>
                <a
                    v-if="statisticsUrl"
                    class="primary-button"
                    :href="statisticsUrl"
                >
                    Synchronize statistics
                </a>
            </section>

            <div class="dashboard-columns">
                <section class="dashboard-section">
                    <header class="section-heading">
                        <div>
                            <p class="panel-label">PC COMPETITIVE</p>
                            <h2>Role overview</h2>
                        </div>
                    </header>
                    <div v-if="roleCards.length" class="rank-grid">
                        <article
                            v-for="role in roleCards"
                            :key="role.role"
                            class="rank-card"
                            :data-role="role.role.toLowerCase()"
                        >
                            <p>{{ role.role }}</p>
                            <strong>
                                {{
                                    role.tier ??
                                    (role.season ? 'Unranked' : 'No rank data')
                                }}
                                <span v-if="role.division">
                                    {{ role.division }}
                                </span>
                            </strong>
                            <small>
                                <template v-if="role.season">
                                    Season {{ role.season }} ·
                                </template>
                                {{ formatNumber(role.games_played, 0) }} games ·
                                {{ formatNumber(role.winrate) }}% win rate · KDA
                                {{ formatNumber(role.kda) }}
                            </small>
                        </article>
                    </div>
                    <p v-else class="empty-copy">
                        Role and rank data will appear after a competitive
                        profile sync.
                    </p>
                </section>

                <section class="dashboard-section">
                    <header class="section-heading">
                        <div>
                            <p class="panel-label">RECENT SNAPSHOTS</p>
                            <h2>Progression</h2>
                        </div>
                    </header>
                    <template v-if="progression">
                        <div class="progress-grid">
                            <article class="progress-card">
                                <p>Win rate</p>
                                <strong>
                                    {{
                                        formatNumber(
                                            progression.winrate.current,
                                        )
                                    }}%
                                </strong>
                                <span
                                    :class="
                                        changeClass(progression.winrate.change)
                                    "
                                >
                                    {{
                                        formatChange(
                                            progression.winrate.change,
                                            'pp',
                                        )
                                    }}
                                </span>
                            </article>
                            <article class="progress-card">
                                <p>Deaths / 10 min</p>
                                <strong>
                                    {{
                                        formatNumber(
                                            progression.deathsPer10.current,
                                        )
                                    }}
                                </strong>
                                <span
                                    :class="
                                        changeClass(
                                            progression.deathsPer10.change,
                                            true,
                                        )
                                    "
                                >
                                    {{
                                        formatChange(
                                            progression.deathsPer10.change,
                                            '',
                                        )
                                    }}
                                </span>
                            </article>
                        </div>
                        <p class="comparison-note">
                            Compared with
                            {{ formatDate(progression.previousCapturedAt) }}
                        </p>
                    </template>
                    <p v-else class="empty-copy">
                        Progression appears after at least two imports with
                        changed statistics.
                    </p>
                </section>
            </div>

            <section class="dashboard-section">
                <header class="section-heading">
                    <div>
                        <p class="panel-label">MOST PLAYED</p>
                        <h2>Top heroes</h2>
                    </div>
                    <a v-if="statisticsUrl" :href="statisticsUrl">
                        Explore all hero statistics
                    </a>
                </header>
                <div v-if="topHeroes.length" class="hero-grid">
                    <article
                        v-for="hero in topHeroes"
                        :key="hero.hero"
                        class="hero-card"
                        :data-role="hero.role.toLowerCase()"
                    >
                        <p class="hero-role">{{ hero.role }}</p>
                        <h3>{{ hero.hero }}</h3>
                        <dl>
                            <div>
                                <dt>Games</dt>
                                <dd>
                                    {{ formatNumber(hero.games_played, 0) }}
                                </dd>
                            </div>
                            <div>
                                <dt>Win rate</dt>
                                <dd>{{ formatNumber(hero.winrate) }}%</dd>
                            </div>
                            <div>
                                <dt>Time</dt>
                                <dd>{{ formatDuration(hero.time_played) }}</dd>
                            </div>
                        </dl>
                    </article>
                </div>
                <p v-else class="empty-copy">
                    Hero breakdowns will appear after your statistics sync.
                </p>
            </section>

            <section class="coach-panel">
                <div>
                    <p class="panel-label">COACH WINSTON</p>
                    <h2>Turn your stats into a game plan.</h2>
                    <p>
                        Ask for advice based on your roles, heroes and
                        progression snapshots.
                    </p>
                </div>
                <a class="primary-button" :href="coachUrl"> Talk to Winston </a>
            </section>
        </section>
    </main>
</template>

<style scoped>
.owgg-dashboard {
    --orange: var(--ow-orange);
    --blue: var(--role-tank);
    position: relative;
    min-height: calc(100dvh - 76px);
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
    margin-inline: auto;
    padding: clamp(24px, 4vh, 42px) 0;
}

.dashboard-header,
.profile-panel,
.dashboard-cards {
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
    text-align: right;
}

.profile-panel,
.stat-card {
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
.stat-card::before {
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

.profile-avatar {
    width: 58px;
    height: 58px;
    flex: 0 0 auto;
    border: 2px solid var(--ow-orange);
    clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%);
    object-fit: cover;
}

.profile-panel h2 {
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
.stat-card span {
    margin-bottom: 0;
    color: var(--text-dim);
    font-size: 0.9rem;
    line-height: 1.5;
}

.secondary-button,
.primary-button {
    display: inline-flex;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    padding: 10px 15px;
    font-family: var(--font-heading);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-align: center;
    text-decoration: none;
    text-transform: uppercase;
    clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 0 100%);
    transition:
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.secondary-button {
    margin-left: auto;
    border: 1px solid var(--surface-line-strong);
    color: var(--text);
    background: var(--bg-0);
}

.primary-button {
    border: 1px solid var(--ow-orange);
    color: var(--bg-0);
    background: var(--ow-orange);
}

.secondary-button:hover,
.primary-button:hover {
    transform: translateY(-2px);
}

.secondary-button:hover {
    border-color: var(--ow-orange);
}

.primary-button:hover {
    background: var(--ow-orange-bright);
}

.secondary-button:focus-visible,
.primary-button:focus-visible {
    outline: 2px solid var(--ow-orange);
    outline-offset: 3px;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
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
    font-size: 2rem;
}

.dashboard-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.dashboard-section,
.empty-panel,
.coach-panel {
    position: relative;
    border: 1px solid var(--surface-line-strong);
    background: var(--bg-1);
    clip-path: polygon(
        0 0,
        calc(100% - 16px) 0,
        100% 16px,
        100% 100%,
        16px 100%,
        0 calc(100% - 16px)
    );
}

.dashboard-section {
    min-width: 0;
    margin-top: 16px;
    padding: 22px;
}

.dashboard-section::before,
.empty-panel::before,
.coach-panel::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 34px;
    height: 3px;
    background: var(--ow-orange);
    content: '';
}

.section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
    padding-bottom: 13px;
    border-bottom: 1px solid var(--surface-line);
}

.section-heading .panel-label {
    margin-bottom: 6px;
}

.section-heading h2,
.empty-panel h2,
.coach-panel h2 {
    margin-bottom: 0;
    font-size: clamp(1.2rem, 2vw, 1.55rem);
    font-style: italic;
    text-transform: uppercase;
}

.section-heading > a {
    color: var(--ow-orange-bright);
    font-size: 0.8rem;
    text-decoration: none;
}

.section-heading > a:hover {
    text-decoration: underline;
}

.rank-grid,
.hero-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.rank-card,
.hero-card,
.progress-card {
    min-width: 0;
    padding: 15px;
    border: 1px solid var(--surface-line);
    background: var(--bg-2);
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
}

.rank-card p,
.hero-role,
.progress-card p {
    margin: 0 0 8px;
    color: var(--text-dim);
    font-family: var(--font-heading);
    font-size: 0.66rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.rank-card strong,
.hero-card h3,
.progress-card strong {
    display: block;
    margin: 0 0 8px;
    color: var(--text);
    font-family: var(--font-heading);
    font-size: 1.25rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    text-transform: uppercase;
}

.rank-card small,
.hero-card dd,
.comparison-note {
    color: var(--text-dim);
    font-size: 0.72rem;
    line-height: 1.5;
}

.rank-card[data-role='tank'] {
    border-top: 2px solid var(--role-tank);
}

.rank-card[data-role='damage'] {
    border-top: 2px solid var(--role-damage);
}

.rank-card[data-role='support'] {
    border-top: 2px solid var(--role-support);
}

.progress-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.progress-card strong {
    font-size: 1.55rem;
}

.progress-card span {
    font-size: 0.75rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}

.trend-positive {
    color: var(--win);
}

.trend-negative {
    color: var(--loss);
}

.trend-neutral {
    color: var(--text-dim);
}

.comparison-note {
    margin: 12px 0 0;
}

.hero-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.hero-card[data-role='tank'] {
    border-left: 3px solid var(--role-tank);
}

.hero-card[data-role='damage'] {
    border-left: 3px solid var(--role-damage);
}

.hero-card[data-role='support'] {
    border-left: 3px solid var(--role-support);
}

.hero-card h3 {
    color: var(--ow-orange-bright);
}

.hero-card dl {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin: 14px 0 0;
}

.hero-card dt {
    color: var(--text-mute);
    font-family: var(--font-heading);
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.hero-card dd {
    margin: 3px 0 0;
    color: var(--text);
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

.empty-panel,
.coach-panel {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-top: 16px;
    padding: 24px;
}

.empty-panel p,
.coach-panel p {
    margin: 8px 0 0;
    color: var(--text-dim);
    font-size: 0.9rem;
    line-height: 1.5;
}

.coach-panel {
    border-color: color-mix(in srgb, var(--ow-orange) 48%, transparent);
}

.empty-copy {
    margin: 0;
    color: var(--text-dim);
    font-size: 0.85rem;
    line-height: 1.5;
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
        min-height: calc(100dvh - 64px);
    }

    .dashboard-content {
        width: min(100% - 28px, 1120px);
        padding: 28px 0;
    }

    .dashboard-header,
    .profile-panel,
    .empty-panel,
    .coach-panel {
        align-items: flex-start;
        flex-direction: column;
    }

    .dashboard-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-columns {
        grid-template-columns: 1fr;
    }

    .rank-grid,
    .hero-grid {
        grid-template-columns: 1fr;
    }

    .secondary-button {
        margin-left: 0;
    }
}

@media (max-width: 420px) {
    .dashboard-cards {
        grid-template-columns: 1fr;
    }

    .progress-grid {
        grid-template-columns: 1fr;
    }
}

@media (prefers-reduced-motion: reduce) {
    .dashboard-header,
    .profile-panel,
    .dashboard-cards {
        animation: none;
    }

    .stat-card {
        transition: none;
    }

    .stat-card:hover {
        transform: none;
    }

    .secondary-button,
    .primary-button {
        transition: none;
    }

    .secondary-button:hover,
    .primary-button:hover {
        transform: none;
    }
}
</style>
