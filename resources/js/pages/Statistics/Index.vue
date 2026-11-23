<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import MetricGrid from '@/components/statistics/MetricGrid.vue';

type MetricValue = number | string | boolean | null;
type MetricMap = Record<string, MetricValue>;

type PlayerProfile = {
    player_id: string;
    name: string;
    avatar: string | null;
    title: string | null;
    last_updated_at: string | null;
};

type Rank = {
    role: string;
    season: string;
    tier: string | null;
    division: number | null;
};

type RoleStats = {
    role: string;
    metrics: MetricMap;
};

type HeroStats = {
    hero: string;
    metrics: MetricMap;
};

type HeroSpecificStat = {
    hero: string;
    key: string;
    value: string | null;
};

const props = defineProps<{
    playerId: string;
    syncing: boolean;
    profile: PlayerProfile | null;
    ranks: Rank[];
    rolesStats: RoleStats[];
    allHeroesStats: MetricMap | null;
    heroStats: HeroStats[];
    heroSpecificStats: HeroSpecificStat[];
}>();

const isSyncing = ref(props.syncing);
let refreshTimer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    if (!isSyncing.value) {
        return;
    }

    let attempts = 0;
    refreshTimer = setInterval(() => {
        attempts += 1;

        router.reload({
            only: [
                'syncing',
                'profile',
                'ranks',
                'rolesStats',
                'allHeroesStats',
                'heroStats',
                'heroSpecificStats',
            ],
            onSuccess: (page) => {
                if (!page.props.syncing) {
                    isSyncing.value = false;
                    clearInterval(refreshTimer);
                }
            },
        });

        if (attempts >= 20) {
            isSyncing.value = false;
            clearInterval(refreshTimer);
        }
    }, 3000);
});

onUnmounted(() => {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});

function updatedAt(value: string | null): string {
    if (!value) {
        return 'Not available';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <Head :title="`${profile?.name ?? playerId} Statistics`" />

    <main class="statistics-page">
        <div class="statistics-grid" aria-hidden="true"></div>
        <div class="statistics-glow" aria-hidden="true"></div>

        <div class="statistics-content">
            <header class="statistics-header">
                <div>
                    <p class="eyebrow">OVERWATCH 2 // PERFORMANCE LAB</p>
                    <h1>PLAYER <strong>STATISTICS</strong></h1>
                    <p class="player-id">{{ playerId }}</p>
                </div>
                <p v-if="profile" class="updated-at">
                    UPDATED {{ updatedAt(profile.last_updated_at) }}
                </p>
            </header>

            <section v-if="isSyncing" class="empty-state" role="status">
                <p class="eyebrow">DATA LINK // OVERFAST</p>
                <h2>Synchronizing statistics</h2>
                <p>
                    Your PC competitive statistics are being imported. This page
                    will update when the import completes.
                </p>
            </section>

            <section v-else-if="!profile" class="empty-state" role="status">
                <p class="eyebrow">DATA LINK // OVERFAST</p>
                <h2>Statistics are not available yet</h2>
                <p>
                    No statistics are stored yet. Check that the Overwatch
                    career profile is public and, when using a database queue,
                    make sure a queue worker is running before refreshing.
                </p>
            </section>

            <template v-else>
                <section class="profile-banner">
                    <img
                        v-if="profile.avatar"
                        :src="profile.avatar"
                        :alt="`${profile.name} avatar`"
                        class="avatar"
                    />
                    <div
                        v-else
                        class="avatar avatar-fallback"
                        aria-hidden="true"
                    >
                        {{ profile.name.slice(0, 1) }}
                    </div>
                    <div>
                        <p class="eyebrow">PLAYER PROFILE</p>
                        <h2>{{ profile.name }}</h2>
                        <p v-if="profile.title" class="profile-title">
                            {{ profile.title }}
                        </p>
                    </div>
                </section>

                <section class="statistics-section">
                    <div class="section-heading">
                        <p class="eyebrow">PC COMPETITIVE</p>
                        <h2>Ranks</h2>
                    </div>
                    <div v-if="ranks.length" class="rank-grid">
                        <article
                            v-for="rank in ranks"
                            :key="`${rank.role}-${rank.season}`"
                            class="rank-card"
                        >
                            <p>{{ rank.role }}</p>
                            <strong>
                                {{ rank.tier ?? 'Unranked' }}
                                <span v-if="rank.division">
                                    {{ rank.division }}
                                </span>
                            </strong>
                            <small>{{ rank.season }}</small>
                        </article>
                    </div>
                    <p v-else class="empty-copy">No rank data is stored yet.</p>
                </section>

                <section class="statistics-section">
                    <div class="section-heading">
                        <p class="eyebrow">PC COMPETITIVE // ALL HEROES</p>
                        <h2>All Heroes</h2>
                    </div>
                    <MetricGrid
                        v-if="allHeroesStats"
                        :metrics="allHeroesStats"
                    />
                    <p v-else class="empty-copy">
                        No all-heroes statistics are stored yet.
                    </p>
                </section>

                <section class="statistics-section">
                    <div class="section-heading">
                        <p class="eyebrow">ROLE BREAKDOWN</p>
                        <h2>Roles</h2>
                    </div>
                    <div v-if="rolesStats.length" class="stack">
                        <article
                            v-for="role in rolesStats"
                            :key="role.role"
                            class="data-card"
                        >
                            <h3>{{ role.role }}</h3>
                            <MetricGrid :metrics="role.metrics" />
                        </article>
                    </div>
                    <p v-else class="empty-copy">
                        No role statistics are stored yet.
                    </p>
                </section>

                <section class="statistics-section">
                    <div class="section-heading">
                        <p class="eyebrow">HERO BREAKDOWN</p>
                        <h2>Heroes</h2>
                    </div>
                    <div v-if="heroStats.length" class="stack">
                        <details
                            v-for="hero in heroStats"
                            :key="hero.hero"
                            class="hero-details"
                        >
                            <summary>
                                <strong>{{ hero.hero }}</strong>
                                <span>
                                    {{ hero.metrics.games_played ?? '—' }}
                                    games
                                </span>
                            </summary>
                            <MetricGrid :metrics="hero.metrics" />
                        </details>
                    </div>
                    <p v-else class="empty-copy">
                        No hero statistics are stored yet.
                    </p>
                </section>

                <section class="statistics-section">
                    <div class="section-heading">
                        <p class="eyebrow">HERO-SPECIFIC</p>
                        <h2>Additional Statistics</h2>
                    </div>
                    <div v-if="heroSpecificStats.length" class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Hero</th>
                                    <th>Statistic</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(stat, index) in heroSpecificStats"
                                    :key="`${stat.hero}-${stat.key}-${index}`"
                                >
                                    <td>{{ stat.hero }}</td>
                                    <td>{{ stat.key }}</td>
                                    <td>{{ stat.value ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="empty-copy">
                        No hero-specific statistics are stored yet.
                    </p>
                </section>
            </template>
        </div>
    </main>
</template>

<style scoped>
.statistics-page {
    --orange: #f06432;
    --blue: #a9e8ff;
    position: relative;
    min-height: calc(100vh - 65px);
    overflow: hidden;
    background:
        radial-gradient(circle at 78% 12%, #18384b 0, transparent 40%),
        linear-gradient(125deg, #091019 0%, #111d29 58%, #0a1119 100%);
    color: #eef6f8;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

.statistics-grid,
.statistics-glow {
    position: absolute;
    pointer-events: none;
}

.statistics-grid {
    inset: 0;
    opacity: 0.12;
    background-image:
        linear-gradient(#8fd9e915 1px, transparent 1px),
        linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 46px 46px;
}

.statistics-glow {
    top: 34%;
    right: -15%;
    width: 32vw;
    height: 32vw;
    border-radius: 999px;
    background: var(--orange);
    filter: blur(100px);
    opacity: 0.1;
}

.statistics-content {
    position: relative;
    z-index: 1;
    width: min(1120px, calc(100% - 48px));
    margin-inline: auto;
    padding: 40px 0 64px;
}

.statistics-header,
.profile-banner,
.statistics-section,
.empty-state {
    animation: statistics-enter 0.4s ease-out both;
}

.statistics-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 28px;
}

.eyebrow {
    margin: 0 0 8px;
    color: var(--orange);
    font-size: 0.68rem;
    letter-spacing: 0.18em;
}

h1,
h2,
h3,
p {
    margin-top: 0;
}

h1 {
    margin-bottom: 8px;
    font-size: clamp(2rem, 5vw, 4rem);
    font-weight: 900;
    letter-spacing: -0.06em;
    line-height: 0.95;
}

h1 strong {
    color: transparent;
    -webkit-text-stroke: 1px var(--blue);
}

.player-id,
.updated-at,
.profile-title,
.empty-copy,
.empty-state > p:last-child {
    margin-bottom: 0;
    color: #a7bec7;
    font-size: 0.9rem;
}

.updated-at {
    font-size: 0.65rem;
    letter-spacing: 0.12em;
    white-space: nowrap;
}

.profile-banner,
.statistics-section,
.empty-state,
.data-card,
.rank-card,
.hero-details,
.table-wrap {
    border: 1px solid #a9e8ff2b;
    background: #09151fdd;
    box-shadow: 0 0 30px #07101866;
}

.profile-banner {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 22px 26px;
    border-left: 3px solid var(--orange);
}

.avatar {
    width: 64px;
    height: 64px;
    flex: 0 0 auto;
    border: 1px solid #a9e8ff66;
    object-fit: cover;
}

.avatar-fallback {
    display: grid;
    place-items: center;
    color: var(--blue);
    font-size: 1.8rem;
}

.profile-banner h2 {
    margin-bottom: 4px;
    font-size: 1.5rem;
}

.profile-title {
    font-size: 0.8rem;
}

.statistics-section,
.empty-state {
    margin-top: 16px;
    padding: 22px;
}

.section-heading {
    margin-bottom: 18px;
}

.section-heading h2,
.empty-state h2 {
    margin-bottom: 0;
    font-size: 1.45rem;
    letter-spacing: 0.03em;
}

.rank-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
}

.rank-card {
    padding: 16px;
}

.rank-card p {
    margin-bottom: 12px;
    color: #a7bec7;
    text-transform: capitalize;
}

.rank-card strong {
    display: block;
    color: var(--blue);
    font-size: 1.2rem;
    text-transform: capitalize;
}

.rank-card small {
    display: block;
    margin-top: 8px;
    color: #88a6b2;
}

.stack {
    display: grid;
    gap: 12px;
}

.data-card,
.hero-details {
    padding: 16px;
}

.data-card h3 {
    margin-bottom: 14px;
    color: var(--blue);
    font-size: 1.1rem;
}

.hero-details summary {
    display: flex;
    cursor: pointer;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: var(--blue);
}

.hero-details summary span {
    color: #88a6b2;
    font-size: 0.8rem;
}

.hero-details[open] summary {
    margin-bottom: 16px;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

th,
td {
    padding: 12px 14px;
    border-bottom: 1px solid #a9e8ff1f;
}

th {
    color: var(--orange);
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

td {
    color: #d3e2e7;
}

tbody tr:last-child td {
    border-bottom: 0;
}

.empty-state {
    padding: 28px;
    border-left: 3px solid var(--orange);
}

.empty-state h2 {
    margin-bottom: 10px;
}

@keyframes statistics-enter {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 700px) {
    .statistics-content {
        width: min(100% - 28px, 1120px);
        padding: 28px 0 44px;
    }

    .statistics-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 12px;
    }

    .statistics-section,
    .empty-state {
        padding: 16px;
    }
}
</style>
