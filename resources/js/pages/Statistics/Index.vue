<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import MetricGrid from '@/components/statistics/MetricGrid.vue';
import { formatStatLabel } from '@/lib/formatStatLabel';

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
const formattedHeroSpecificStats = computed(() =>
    props.heroSpecificStats.map((stat, index) => ({
        ...stat,
        ...formatStatLabel(stat.key),
        rowKey: `${stat.hero}-${stat.key}-${index}`,
    })),
);
const rankColors: Record<string, string> = {
    bronze: 'var(--rank-bronze)',
    silver: 'var(--rank-silver)',
    gold: 'var(--rank-gold)',
    platinum: 'var(--rank-platinum)',
    diamond: 'var(--rank-diamond)',
    master: 'var(--rank-master)',
    grandmaster: 'var(--rank-grandmaster)',
    champion: 'var(--rank-champion)',
};
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

function rankColor(tier: string | null): string {
    return (
        rankColors[tier?.toLowerCase().replaceAll(' ', '') ?? ''] ??
        'var(--text-dim)'
    );
}
</script>

<template>
    <Head :title="`${profile?.name ?? playerId} Statistics`" />

    <main class="statistics-page">
        <div class="statistics-grid" aria-hidden="true"></div>

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
                <div class="sync-loader" aria-hidden="true"></div>
                <div>
                    <p class="eyebrow">DATA LINK // OVERFAST</p>
                    <h2>Synchronizing statistics</h2>
                    <p>
                        Your PC competitive statistics are being imported. This
                        page will update when the import completes.
                    </p>
                </div>
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
                            :data-role="rank.role.toLowerCase()"
                        >
                            <p class="rank-role">
                                <span
                                    class="role-mark"
                                    aria-hidden="true"
                                ></span>
                                {{ rank.role }}
                            </p>
                            <strong
                                class="rank-badge"
                                :style="{
                                    '--rank-color': rankColor(rank.tier),
                                }"
                            >
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
                    <div v-if="rolesStats.length" class="role-grid">
                        <article
                            v-for="role in rolesStats"
                            :key="role.role"
                            class="data-card"
                            :data-role="role.role.toLowerCase()"
                        >
                            <h3>
                                <span
                                    class="role-mark"
                                    aria-hidden="true"
                                ></span>
                                {{ role.role }}
                            </h3>
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
                                    v-for="stat in formattedHeroSpecificStats"
                                    :key="stat.rowKey"
                                >
                                    <td>{{ stat.hero }}</td>
                                    <td>
                                        <span class="stat-name">
                                            {{ stat.label }}
                                        </span>
                                        <span
                                            v-if="stat.qualifier"
                                            class="stat-qualifier"
                                        >
                                            {{ stat.qualifier }}
                                        </span>
                                    </td>
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
    --orange: var(--ow-orange);
    --blue: var(--role-tank);
    position: relative;
    min-height: calc(100vh - 65px);
    overflow: hidden;
    background:
        radial-gradient(ellipse at 86% 4%, var(--bg-3) 0, transparent 38%),
        var(--bg-0);
    color: var(--text);
    font-family: var(--font-ui);
}

.statistics-grid {
    position: absolute;
    inset: 0;
    opacity: 0.04;
    background-image: repeating-linear-gradient(
        135deg,
        transparent 0 15px,
        var(--text) 15px 16px
    );
    pointer-events: none;
}

.statistics-content {
    position: relative;
    z-index: 1;
    width: min(1180px, calc(100% - 48px));
    margin-inline: auto;
    padding: 36px 0 56px;
}

.statistics-header,
.profile-banner,
.statistics-section,
.empty-state {
    animation: statistics-enter 300ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
}

.statistics-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 30px;
}

.eyebrow {
    margin: 0 0 8px;
    color: var(--orange);
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.18em;
    text-transform: uppercase;
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
    font-style: italic;
    text-transform: uppercase;
}

h1 strong {
    color: var(--orange);
}

.player-id,
.updated-at,
.profile-title,
.empty-copy,
.empty-state > p:last-child {
    margin-bottom: 0;
    color: var(--text-dim);
    font-size: 0.9rem;
}

.updated-at {
    font-size: 0.65rem;
    letter-spacing: 0.12em;
    white-space: nowrap;
    font-family: var(--font-heading);
    font-weight: 800;
    text-transform: uppercase;
}

.profile-banner,
.statistics-section,
.empty-state,
.data-card,
.rank-card,
.hero-details,
.table-wrap {
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

.profile-banner {
    position: relative;
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 22px 26px;
    border-left: 4px solid var(--orange);
    background: var(--bg-2);
}

.avatar {
    width: 64px;
    height: 64px;
    flex: 0 0 auto;
    border: 2px solid var(--ow-orange);
    clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%);
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
    font-style: italic;
    text-transform: uppercase;
}

.profile-title {
    font-size: 0.8rem;
}

.statistics-section,
.empty-state {
    position: relative;
    margin-top: 16px;
    padding: 22px;
    background: var(--bg-1);
}

.statistics-section::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 34px;
    height: 3px;
    background: var(--ow-orange);
    content: '';
}

.section-heading {
    display: block;
    margin-bottom: 18px;
    padding-bottom: 13px;
    border-bottom: 1px solid var(--surface-line);
}

.section-heading h2,
.empty-state h2 {
    margin-bottom: 0;
    color: var(--text);
    font-size: clamp(1.25rem, 2.5vw, 1.55rem);
    font-weight: 850;
    letter-spacing: 0.02em;
    font-style: italic;
    text-transform: uppercase;
}

.rank-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
}

.rank-card {
    position: relative;
    overflow: hidden;
    padding: 16px;
    background: var(--bg-2);
    transition:
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.rank-card[data-role],
.data-card[data-role] {
    --role-color: var(--role-tank);
}

.rank-card[data-role='damage'],
.data-card[data-role='damage'] {
    --role-color: var(--role-damage);
}

.rank-card[data-role='support'],
.data-card[data-role='support'] {
    --role-color: var(--role-support);
}

.rank-card::before {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 4px;
    background: var(--role-color);
    content: '';
}

.rank-card:hover {
    transform: translateY(-3px);
    border-color: color-mix(in srgb, var(--role-color) 58%, transparent);
}

.rank-role {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    color: var(--role-color);
    font-family: var(--font-heading);
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: capitalize;
}

.role-mark {
    display: inline-block;
    width: 11px;
    height: 11px;
    flex: 0 0 auto;
    background: var(--role-color, var(--ow-orange));
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
}

.rank-badge {
    display: inline-flex;
    align-items: baseline;
    gap: 8px;
    padding: 5px 9px;
    border: 1px solid color-mix(in srgb, var(--rank-color) 46%, transparent);
    background: color-mix(in srgb, var(--rank-color) 12%, transparent);
    clip-path: polygon(0 0, calc(100% - 9px) 0, 100% 9px, 100% 100%, 0 100%);
    color: var(--rank-color);
    font-size: 1.2rem;
    text-transform: capitalize;
}

.rank-badge span {
    font-size: 0.85rem;
    font-variant-numeric: tabular-nums;
}

.rank-card small {
    display: block;
    margin-top: 8px;
    color: var(--text-mute);
    font-family: var(--font-heading);
    letter-spacing: 0.08em;
}

.stack {
    display: grid;
    gap: 12px;
}

.role-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 370px), 1fr));
    gap: 12px;
}

.data-card,
.hero-details {
    padding: 16px;
    background: var(--bg-2);
}

.data-card h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    color: var(--role-color, var(--ow-orange));
    font-size: 1.1rem;
    font-weight: 800;
    text-transform: capitalize;
    font-style: italic;
}

.hero-details summary {
    display: flex;
    min-height: 34px;
    cursor: pointer;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: var(--text);
    font-family: var(--font-heading);
    font-size: 1rem;
    font-weight: 800;
    text-transform: uppercase;
}

.hero-details summary:focus-visible {
    outline: 2px solid var(--ow-orange);
    outline-offset: 4px;
}

.hero-details summary span {
    color: var(--text-dim);
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
    padding: 13px 15px;
    border-bottom: 1px solid var(--surface-line);
}

th {
    color: var(--text-dim);
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    font-family: var(--font-heading);
}

td {
    color: var(--text);
}

tbody tr {
    transition: background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

tbody tr:nth-child(even) {
    background: var(--bg-2);
}

tbody tr:hover {
    background: var(--bg-3);
}

tbody tr:hover td:first-child {
    box-shadow: inset 3px 0 0 var(--ow-orange);
}

td:last-child {
    color: var(--text);
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.stat-name {
    font-weight: 650;
}

.stat-qualifier {
    display: inline-flex;
    margin-left: 8px;
    padding: 3px 7px;
    border: 1px solid color-mix(in srgb, var(--ow-orange) 50%, transparent);
    background: color-mix(in srgb, var(--ow-orange) 11%, var(--bg-1));
    color: var(--ow-orange-bright);
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    line-height: 1.2;
    text-transform: uppercase;
    vertical-align: middle;
}

tbody tr:last-child td {
    border-bottom: 0;
}

.empty-state {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 28px;
    border-left: 4px solid var(--orange);
}

.empty-state h2 {
    margin-bottom: 10px;
}

.empty-state > p:last-child {
    max-width: 62ch;
    line-height: 1.65;
}

.sync-loader {
    width: 34px;
    height: 34px;
    flex: 0 0 auto;
    border: 3px solid var(--surface-line-strong);
    border-top-color: var(--ow-orange);
    border-right-color: var(--ow-orange);
    clip-path: polygon(25% 0, 75% 0, 100% 50%, 75% 100%, 25% 100%, 0 50%);
    animation: statistics-loader 750ms linear infinite;
}

.empty-state > div > p:last-child {
    max-width: 62ch;
    margin-bottom: 0;
    color: var(--text-dim);
    font-size: 0.9rem;
    line-height: 1.65;
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

@keyframes statistics-loader {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .statistics-header,
    .profile-banner,
    .statistics-section,
    .empty-state,
    .rank-card {
        animation: none;
        transition: none;
    }

    .rank-card:hover {
        transform: none;
    }

    .sync-loader {
        animation: none;
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

    .empty-state {
        align-items: flex-start;
        gap: 14px;
    }

    .profile-banner {
        align-items: flex-start;
        padding: 17px;
    }

    .avatar {
        width: 54px;
        height: 54px;
    }

    th,
    td {
        padding: 11px 12px;
    }

    .stat-qualifier {
        margin: 5px 0 0;
    }
}
</style>
