<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowRight,
    Crosshair,
    HeartPulse,
    Search,
    Shield,
    Swords,
} from '@lucide/vue';
import { computed, ref } from 'vue';

type HeroRole = 'tank' | 'damage' | 'support';
type RoleFilter = 'all' | HeroRole;

type CounterPick = {
    key: string;
    name: string;
    reason: string;
    winrate: number | null;
    winrateDifference: number | null;
};

type TierListHero = {
    key: string;
    name: string;
    role: HeroRole;
    winrate: number;
    pickrate: number | null;
};

type MapRecommendation = {
    key: string;
    name: string;
    reason: string;
};

type HeroGuide = {
    styles: { key: string; label: string }[];
    strengths: string[];
    weaknesses: string[];
    counters: Record<HeroRole, CounterPick[]>;
    maps: {
        favorable: MapRecommendation[];
        difficult: MapRecommendation[];
    };
    confidence: 'curated' | 'inferred';
};

type CounterwatchHero = {
    key: string;
    name: string;
    role: HeroRole;
    subrole: string | null;
    guide: HeroGuide;
};

type CompositionCycleItem = {
    key: string;
    label: string;
    beats: string;
    description: string;
};

const props = defineProps<{
    heroes: CounterwatchHero[];
    catalogError: string | null;
    mapCatalogError: string | null;
    heroStatsError: string | null;
    availableMaps: { key: string; name: string }[];
    tierList: TierListHero[];
    selectedMapKey: string | null;
    selectedMapName: string | null;
    selectedHeroKey: string | null;
    statsScope: string;
    compositionGuide: {
        cycle: CompositionCycleItem[];
        caveat: string;
    };
    dataNotice: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Counterwatch', href: '/counterwatch' }],
    },
});

const roleFilters: { key: RoleFilter; label: string }[] = [
    { key: 'all', label: 'Tous' },
    { key: 'tank', label: 'Tank' },
    { key: 'damage', label: 'Dégâts' },
    { key: 'support', label: 'Soutien' },
];
const roleNames: Record<HeroRole, string> = {
    tank: 'Tank',
    damage: 'Dégâts',
    support: 'Soutien',
};
const roleIcons = {
    tank: Shield,
    damage: Crosshair,
    support: HeartPulse,
};
const roleFilter = ref<RoleFilter>('all');
const tierRoleFilter = ref<RoleFilter>('all');
const query = ref('');
const searchOpen = ref(false);
const selectedKey = ref(props.selectedHeroKey ?? props.heroes[0]?.key ?? '');
const selectedHero = computed(
    () => props.heroes.find((hero) => hero.key === selectedKey.value) ?? null,
);
const counterRoles: HeroRole[] = ['tank', 'damage', 'support'];

const sortedHeroes = computed(() =>
    [...props.heroes].sort((a, b) => a.name.localeCompare(b.name, 'fr')),
);
const filteredHeroes = computed(() => {
    const normalizedQuery = query.value
        .trim()
        .toLocaleLowerCase('fr')
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '');

    return sortedHeroes.value
        .filter(
            (hero) =>
                roleFilter.value === 'all' || hero.role === roleFilter.value,
        )
        .filter((hero) => {
            if (!normalizedQuery) {
                return true;
            }

            const searchableName = `${hero.name} ${hero.key}`
                .toLocaleLowerCase('fr')
                .normalize('NFD')
                .replace(/\p{Diacritic}/gu, '');

            return searchableName.includes(normalizedQuery);
        })
        .slice(0, 8);
});
const sortedTierList = computed(() =>
    [...props.tierList]
        .filter(
            (hero) =>
                tierRoleFilter.value === 'all' ||
                hero.role === tierRoleFilter.value,
        )
        .sort((a, b) => b.winrate - a.winrate || a.name.localeCompare(b.name)),
);
const tierGroups = computed(() =>
    ['S', 'A', 'B', 'C', 'D']
        .map((tier) => ({
            tier,
            heroes: sortedTierList.value.filter(
                (hero) => tierFor(hero.winrate) === tier,
            ),
        }))
        .filter((group) => group.heroes.length > 0),
);

function chooseHero(hero: CounterwatchHero): void {
    selectedKey.value = hero.key;
    query.value = '';
    searchOpen.value = false;
}

function roleClass(role: HeroRole): string {
    return `role-${role}`;
}

function tierFor(winrate: number): string {
    if (winrate >= 53) {
        return 'S';
    }

    if (winrate >= 51) {
        return 'A';
    }

    if (winrate >= 49) {
        return 'B';
    }

    if (winrate >= 47) {
        return 'C';
    }

    return 'D';
}

function changeMap(event: Event): void {
    const target = event.target;
    if (!(target instanceof HTMLSelectElement)) {
        return;
    }

    router.get(
        '/counterwatch',
        {
            ...(target.value ? { map: target.value } : {}),
            ...(selectedKey.value ? { hero: selectedKey.value } : {}),
        },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Counterwatch — Matchups & compositions" />

    <main class="counterwatch-page">
        <div class="counterwatch-grid" aria-hidden="true"></div>
        <div class="counterwatch-content">
            <header class="page-hero">
                <div class="page-hero-copy">
                    <p class="eyebrow">
                        <Swords class="eyebrow-icon" /> MATCHUP INTEL // OWGG
                    </p>
                    <h1>COUNTERWATCH</h1>
                    <p class="page-intro">
                        Comprends le plan adverse, choisis une réponse et
                        adapte-la à la carte. Un contre n’est jamais automatique
                        : le timing et l’équipe comptent.
                    </p>
                </div>
                <div class="search-wrapper">
                    <div class="search-panel">
                        <label for="hero-search">ANALYSER UN HÉROS</label>
                        <div class="search-control">
                            <Search class="search-icon" aria-hidden="true" />
                            <input
                                id="hero-search"
                                v-model="query"
                                type="search"
                                autocomplete="off"
                                placeholder="Ex. Ana, Pharah, D.Va…"
                                aria-controls="hero-search-results"
                                :aria-expanded="
                                    searchOpen && filteredHeroes.length > 0
                                "
                                @focus="searchOpen = true"
                                @keydown.esc="searchOpen = false"
                            />
                        </div>
                        <div
                            class="role-filters"
                            aria-label="Filtrer les héros par rôle"
                        >
                            <button
                                v-for="filter in roleFilters"
                                :key="filter.key"
                                type="button"
                                :aria-pressed="roleFilter === filter.key"
                                :class="{ active: roleFilter === filter.key }"
                                @click="roleFilter = filter.key"
                            >
                                {{ filter.label }}
                            </button>
                        </div>
                    </div>
                    <div
                        v-if="searchOpen && filteredHeroes.length > 0"
                        id="hero-search-results"
                        class="search-results"
                        role="listbox"
                        aria-label="Héros correspondants"
                    >
                        <button
                            v-for="hero in filteredHeroes"
                            :key="hero.key"
                            type="button"
                            class="search-result"
                            role="option"
                            :aria-selected="selectedKey === hero.key"
                            @mousedown.prevent="chooseHero(hero)"
                            @click="chooseHero(hero)"
                        >
                            <span
                                class="role-mark"
                                :class="roleClass(hero.role)"
                            >
                                <component
                                    :is="roleIcons[hero.role]"
                                    aria-hidden="true"
                                />
                            </span>
                            <span>{{ hero.name }}</span>
                            <small>{{ roleNames[hero.role] }}</small>
                        </button>
                    </div>
                    <p
                        v-else-if="
                            searchOpen && query && !filteredHeroes.length
                        "
                        class="no-results"
                    >
                        Aucun héros ne correspond à cette recherche.
                    </p>
                </div>
            </header>

            <section v-if="catalogError" class="catalog-error" role="status">
                <strong>Roster indisponible</strong>
                <span>{{ catalogError }}</span>
            </section>

            <section
                class="tierlist-section ow-panel"
                aria-labelledby="tierlist-title"
            >
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">
                            MÉTA // STATISTIQUES DE CLASSEMENT
                        </p>
                        <h2 id="tierlist-title">TIER LIST PAR WINRATE</h2>
                    </div>
                    <span class="section-tag">{{ statsScope }}</span>
                </div>
                <div class="tierlist-controls">
                    <label for="stats-map">CARTE</label>
                    <select
                        id="stats-map"
                        :value="selectedMapKey ?? ''"
                        @change="changeMap"
                    >
                        <option value="">Toutes les cartes</option>
                        <option
                            v-for="map in availableMaps"
                            :key="map.key"
                            :value="map.key"
                        >
                            {{ map.name }}
                        </option>
                    </select>
                    <div
                        class="role-filters tier-role-filters"
                        aria-label="Filtrer la tier list par rôle"
                    >
                        <button
                            v-for="filter in roleFilters"
                            :key="filter.key"
                            type="button"
                            :aria-pressed="tierRoleFilter === filter.key"
                            :class="{ active: tierRoleFilter === filter.key }"
                            @click="tierRoleFilter = filter.key"
                        >
                            {{ filter.label }}
                        </button>
                    </div>
                </div>
                <p class="tierlist-note">
                    Source : OverFast (cache d’une heure). Le pickrate mesure la
                    popularité, pas la taille exacte de l’échantillon. Les rangs
                    OWGG se basent uniquement sur le winrate : S ≥ 53 %, A ≥ 51
                    %, B ≥ 49 %, C ≥ 47 %, D &lt; 47 %. Ce n’est pas une mesure
                    absolue de puissance.
                </p>
                <p v-if="heroStatsError" class="map-error" role="status">
                    {{ heroStatsError }}
                </p>
                <div v-else-if="tierGroups.length" class="tier-groups">
                    <section
                        v-for="group in tierGroups"
                        :key="group.tier"
                        class="tier-group"
                        :class="`tier-${group.tier.toLowerCase()}`"
                    >
                        <h3>{{ group.tier }}</h3>
                        <ol>
                            <li v-for="hero in group.heroes" :key="hero.key">
                                <button
                                    type="button"
                                    :class="{
                                        selected: selectedKey === hero.key,
                                    }"
                                    @click="selectedKey = hero.key"
                                >
                                    <span class="tier-hero-name">
                                        {{ hero.name }}
                                        <small>{{
                                            roleNames[hero.role]
                                        }}</small>
                                    </span>
                                    <span class="tier-hero-stats">
                                        <strong
                                            >{{
                                                hero.winrate.toFixed(1)
                                            }}%</strong
                                        >
                                        <small v-if="hero.pickrate !== null">
                                            Pick {{ hero.pickrate.toFixed(1) }}%
                                        </small>
                                    </span>
                                </button>
                            </li>
                        </ol>
                    </section>
                </div>
                <p v-else class="tierlist-empty">
                    Aucune statistique n’est disponible pour ce filtre.
                </p>
            </section>

            <template v-if="selectedHero">
                <section
                    class="hero-overview ow-panel"
                    aria-labelledby="selected-hero"
                >
                    <div class="hero-heading">
                        <div>
                            <p class="eyebrow">
                                DOSSIER // {{ roleNames[selectedHero.role] }}
                            </p>
                            <h2 id="selected-hero">{{ selectedHero.name }}</h2>
                        </div>
                        <span
                            class="hero-role"
                            :class="roleClass(selectedHero.role)"
                        >
                            <component
                                :is="roleIcons[selectedHero.role]"
                                aria-hidden="true"
                            />
                            {{ roleNames[selectedHero.role] }}
                        </span>
                    </div>

                    <div class="style-row" aria-label="Styles de composition">
                        <span class="section-label">STYLE DE JEU</span>
                        <span
                            v-for="style in selectedHero.guide.styles"
                            :key="style.key"
                            class="style-chip"
                            :class="`style-${style.key}`"
                        >
                            {{ style.label }}
                        </span>
                        <span
                            v-if="!selectedHero.guide.styles.length"
                            class="style-chip style-unknown"
                        >
                            À classifier
                        </span>
                        <span
                            class="profile-confidence"
                            :class="{
                                inferred:
                                    selectedHero.guide.confidence ===
                                    'inferred',
                            }"
                        >
                            {{
                                selectedHero.guide.confidence === 'curated'
                                    ? 'PROFIL OWGG'
                                    : 'CLASSIFICATION ESTIMÉE'
                            }}
                        </span>
                    </div>

                    <div class="strength-weakness">
                        <article class="note-card strengths">
                            <h3>
                                <span class="note-mark">+</span> POINTS FORTS
                            </h3>
                            <ul>
                                <li
                                    v-for="strength in selectedHero.guide
                                        .strengths"
                                    :key="strength"
                                >
                                    {{ strength }}
                                </li>
                            </ul>
                        </article>
                        <article class="note-card weaknesses">
                            <h3>
                                <span class="note-mark">−</span> POINTS DE
                                VIGILANCE
                            </h3>
                            <ul>
                                <li
                                    v-for="weakness in selectedHero.guide
                                        .weaknesses"
                                    :key="weakness"
                                >
                                    {{ weakness }}
                                </li>
                            </ul>
                        </article>
                    </div>
                </section>

                <section class="map-section" aria-labelledby="map-title">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">
                                GÉOMÉTRIE // ENVIRONNEMENT DE JEU
                            </p>
                            <h2 id="map-title">SES CARTES FAVORABLES</h2>
                        </div>
                        <span class="section-tag"
                            >3 FAVORABLES · 3 EXIGEANTES</span
                        >
                    </div>
                    <p class="map-intro">
                        Ces tendances viennent de la géométrie des cartes, pas
                        de statistiques de victoire. Le point, le côté attaque /
                        défense et la composition adverse peuvent changer le
                        résultat.
                    </p>
                    <p v-if="mapCatalogError" class="map-error" role="status">
                        {{ mapCatalogError }}
                    </p>
                    <div class="map-columns">
                        <article class="map-set favorable ow-panel">
                            <h3>CARTES OÙ IL EXCELLE</h3>
                            <ol v-if="selectedHero.guide.maps.favorable.length">
                                <li
                                    v-for="map in selectedHero.guide.maps
                                        .favorable"
                                    :key="map.key"
                                >
                                    <strong>{{ map.name }}</strong>
                                    <p>{{ map.reason }}</p>
                                </li>
                            </ol>
                            <p v-else class="map-empty">
                                Les cartes ne sont pas disponibles pour le
                                moment.
                            </p>
                        </article>
                        <article class="map-set difficult ow-panel">
                            <h3>CARTES PLUS EXIGEANTES</h3>
                            <ol v-if="selectedHero.guide.maps.difficult.length">
                                <li
                                    v-for="map in selectedHero.guide.maps
                                        .difficult"
                                    :key="map.key"
                                >
                                    <strong>{{ map.name }}</strong>
                                    <p>{{ map.reason }}</p>
                                </li>
                            </ol>
                            <p v-else class="map-empty">
                                Les cartes ne sont pas disponibles pour le
                                moment.
                            </p>
                        </article>
                    </div>
                    <a
                        class="counterwatch-external"
                        href="https://www.counterwatch.gg/stats/overwatch/team-builder"
                        target="_blank"
                        rel="noreferrer"
                    >
                        Pour davantage d’informations, consulter Counterwatch
                        <ArrowRight aria-hidden="true" />
                    </a>
                </section>

                <section
                    class="counter-section"
                    aria-labelledby="counter-title"
                >
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">
                                ADAPTATION // OPTIONS DE RÉPONSE
                            </p>
                            <h2 id="counter-title">COMMENT LE CONTESTER</h2>
                        </div>
                        <span class="section-tag">3 OPTIONS PAR RÔLE</span>
                    </div>
                    <p class="counter-intro">
                        Les matchups héros-à-héros enregistrés passent en
                        premier; les options restantes s’appuient sur le style
                        de jeu du héros. Ce sont des pistes à adapter à la
                        carte, à la composition et à l’exécution.
                    </p>
                    <p class="counter-stats-note">
                        Le winrate compare la performance globale de chaque
                        héros{{
                            selectedMapName ? ` sur ${selectedMapName}` : ''
                        }}; ce n’est pas son taux de victoire contre
                        {{ selectedHero.name }}.
                    </p>

                    <div class="counter-grid">
                        <article
                            v-for="role in counterRoles"
                            :key="role"
                            class="counter-card ow-panel"
                            :class="roleClass(role)"
                        >
                            <header>
                                <span
                                    class="role-mark"
                                    :class="roleClass(role)"
                                >
                                    <component
                                        :is="roleIcons[role]"
                                        aria-hidden="true"
                                    />
                                </span>
                                <h3>{{ roleNames[role] }}</h3>
                                <span class="counter-count">
                                    {{
                                        selectedHero.guide.counters[role].length
                                    }}
                                </span>
                            </header>
                            <ol v-if="selectedHero.guide.counters[role].length">
                                <li
                                    v-for="(pick, index) in selectedHero.guide
                                        .counters[role]"
                                    :key="pick.key"
                                    class="counter-pick"
                                >
                                    <span class="pick-number"
                                        >0{{ index + 1 }}</span
                                    >
                                    <div>
                                        <strong>{{ pick.name }}</strong>
                                        <p>{{ pick.reason }}</p>
                                        <p
                                            v-if="pick.winrate !== null"
                                            class="counter-pick-stats"
                                        >
                                            {{ pick.winrate.toFixed(1) }}% de
                                            winrate
                                            <span
                                                v-if="
                                                    pick.winrateDifference !==
                                                    null
                                                "
                                            >
                                                ·
                                                {{
                                                    pick.winrateDifference > 0
                                                        ? '+'
                                                        : ''
                                                }}{{
                                                    pick.winrateDifference.toFixed(
                                                        1,
                                                    )
                                                }}
                                                pts vs
                                                {{ selectedHero.name }}
                                            </span>
                                        </p>
                                    </div>
                                </li>
                            </ol>
                            <p v-else class="counter-empty">
                                Le roster reçu ne contient pas assez de héros
                                pour proposer trois réponses dans ce rôle.
                            </p>
                        </article>
                    </div>
                    <p class="data-notice">
                        {{ dataNotice }}
                        <a
                            href="https://overfast-api.tekrop.fr/docs"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Sources du roster et des cartes : OverFast
                        </a>
                    </p>
                </section>
            </template>

            <section
                class="composition-section ow-panel"
                aria-labelledby="composition-title"
            >
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">
                            COMPRENDRE // LE CYCLE DES COMPOSITIONS
                        </p>
                        <h2 id="composition-title">POKE · BRAWL · DIVE</h2>
                    </div>
                    <span class="section-tag">TENDANCES, PAS RÈGLES</span>
                </div>
                <p class="cycle-intro">
                    Pense à un cycle de pierre-feuille-ciseaux : chaque style a
                    un avantage fréquent sur un autre, mais la carte et
                    l’exécution peuvent tout changer.
                </p>
                <div class="cycle-grid">
                    <article
                        v-for="(style, index) in compositionGuide.cycle"
                        :key="style.key"
                        class="cycle-card"
                        :class="`style-${style.key}`"
                    >
                        <div class="cycle-card-heading">
                            <span class="cycle-index">0{{ index + 1 }}</span>
                            <h3>{{ style.label }}</h3>
                        </div>
                        <p>{{ style.description }}</p>
                        <div class="beats">
                            <ArrowRight aria-hidden="true" />
                            <span>AVANTAGE SOUVENT CONTRE</span>
                            <strong>{{
                                compositionGuide.cycle.find(
                                    (item) => item.key === style.beats,
                                )?.label
                            }}</strong>
                        </div>
                    </article>
                </div>
                <p class="cycle-caveat">
                    <ArrowDown aria-hidden="true" />
                    {{ compositionGuide.caveat }}
                </p>
            </section>

            <aside class="coach-callout">
                <span class="coach-icon"><Swords aria-hidden="true" /></span>
                <div>
                    <p class="eyebrow">BESOIN D’UN PLAN PLUS PRÉCIS ?</p>
                    <p>
                        Coach Winston peut relier le matchup aux capacités, aux
                        perks et à tes statistiques. Décris la composition
                        adverse, la carte et ton rôle.
                    </p>
                </div>
                <a href="/chat" class="coach-link"
                    >PARLER AU COACH <ArrowRight aria-hidden="true"
                /></a>
            </aside>
        </div>
    </main>
</template>

<style scoped>
.counterwatch-page {
    position: relative;
    min-height: 100%;
    overflow: hidden;
    background:
        radial-gradient(
            ellipse at 15% 0%,
            color-mix(in srgb, var(--role-tank) 11%, transparent),
            transparent 36rem
        ),
        var(--bg-0);
    color: var(--text);
}

.counterwatch-grid {
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0.035;
    background-image:
        linear-gradient(30deg, var(--text) 1px, transparent 1px),
        linear-gradient(150deg, var(--text) 1px, transparent 1px);
    background-size: 34px 58px;
    mask-image: linear-gradient(to bottom, black, transparent 80%);
}

.counterwatch-content {
    position: relative;
    width: min(1380px, 100%);
    margin-inline: auto;
    padding: clamp(24px, 4vw, 56px);
}

.page-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(290px, 380px);
    align-items: end;
    gap: clamp(28px, 5vw, 72px);
    margin-bottom: 30px;
}

.eyebrow,
.section-label,
.search-panel > label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    color: var(--ow-orange);
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.eyebrow-icon {
    width: 15px;
    height: 15px;
}

.page-hero h1 {
    margin: 14px 0 10px;
    font-family: var(--font-heading);
    font-size: clamp(2.7rem, 7vw, 5.7rem);
    font-style: italic;
    font-weight: 900;
    letter-spacing: -0.055em;
    line-height: 0.92;
}

.page-intro {
    max-width: 62ch;
    margin: 18px 0 0;
    color: var(--text-dim);
    font-size: 0.94rem;
    line-height: 1.65;
}

.search-wrapper {
    position: relative;
    z-index: 10;
    min-width: 0;
}

.search-panel {
    position: relative;
    padding: 18px;
    border: 1px solid var(--surface-line);
    background: var(--bg-2);
    clip-path: polygon(
        0 0,
        calc(100% - 13px) 0,
        100% 13px,
        100% 100%,
        13px 100%,
        0 calc(100% - 13px)
    );
}

.search-control {
    position: relative;
    margin-top: 11px;
}

.search-control input {
    width: 100%;
    height: 46px;
    padding: 0 12px 0 40px;
    border: 0;
    border-bottom: 2px solid var(--surface-line-strong);
    border-radius: 0;
    outline: none;
    background: var(--bg-1);
    color: var(--text);
    font: inherit;
    transition: border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.search-control input::placeholder {
    color: var(--text-mute);
}

.search-control input:focus {
    border-color: var(--ow-orange);
}

.search-icon {
    position: absolute;
    top: 14px;
    left: 12px;
    width: 17px;
    height: 17px;
    color: var(--text-mute);
}

.role-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 11px;
}

.role-filters button {
    min-height: 30px;
    padding: 0 10px;
    border: 1px solid var(--surface-line);
    background: var(--bg-1);
    color: var(--text-dim);
    cursor: pointer;
    font-size: 0.63rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition: 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.role-filters button:hover,
.role-filters button.active {
    border-color: var(--ow-orange);
    background: color-mix(in srgb, var(--ow-orange) 13%, var(--bg-1));
    color: var(--text);
}

button:focus-visible,
a:focus-visible,
input:focus-visible {
    outline: 2px solid var(--ow-orange);
    outline-offset: 3px;
}

.search-results {
    position: absolute;
    z-index: 5;
    top: calc(100% - 16px);
    right: 18px;
    left: 18px;
    max-height: 280px;
    overflow-y: auto;
    border: 1px solid var(--surface-line-strong);
    background: var(--bg-2);
    box-shadow: 0 12px 32px color-mix(in srgb, var(--bg-0) 65%, transparent);
}

.search-result {
    display: flex;
    width: 100%;
    min-height: 42px;
    align-items: center;
    gap: 10px;
    padding: 7px 10px;
    border: 0;
    border-bottom: 1px solid var(--surface-line);
    background: transparent;
    color: var(--text);
    cursor: pointer;
    text-align: left;
}

.search-result:hover,
.search-result[aria-selected='true'] {
    background: var(--bg-3);
}

.search-result small {
    margin-left: auto;
    color: var(--text-mute);
    font-size: 0.64rem;
    text-transform: uppercase;
}

.no-results {
    position: absolute;
    z-index: 5;
    top: calc(100% - 16px);
    right: 18px;
    left: 18px;
    margin: 0;
    padding: 14px;
    border: 1px solid var(--surface-line-strong);
    background: var(--bg-2);
    color: var(--text-dim);
    font-size: 0.8rem;
}

.ow-panel {
    border: 1px solid var(--surface-line);
    background: var(--bg-2);
    clip-path: polygon(
        0 0,
        calc(100% - 15px) 0,
        100% 15px,
        100% 100%,
        15px 100%,
        0 calc(100% - 15px)
    );
}

.hero-overview {
    padding: clamp(20px, 3vw, 34px);
}

.hero-heading,
.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.hero-heading h2,
.section-heading h2 {
    margin: 7px 0 0;
    font-family: var(--font-heading);
    font-size: clamp(1.75rem, 4vw, 2.7rem);
    font-style: italic;
    font-weight: 900;
    letter-spacing: -0.025em;
    text-transform: uppercase;
}

.hero-role {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border: 1px solid currentColor;
    color: var(--role-color);
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.hero-role svg,
.role-mark svg {
    width: 16px;
    height: 16px;
}

.role-tank {
    --role-color: var(--role-tank);
}

.role-damage {
    --role-color: var(--role-damage);
}

.role-support {
    --role-color: var(--role-support);
}

.style-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 9px;
    margin-top: 25px;
    padding-top: 17px;
    border-top: 1px solid var(--surface-line);
}

.section-label {
    margin-right: 4px;
    color: var(--text-mute);
    font-size: 0.58rem;
}

.style-chip,
.section-tag {
    display: inline-flex;
    align-items: center;
    min-height: 27px;
    padding: 0 10px;
    border: 1px solid var(--style-color, var(--surface-line-strong));
    color: var(--style-color, var(--text-dim));
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}

.style-dive {
    --style-color: var(--role-damage);
}

.style-brawl {
    --style-color: var(--ow-orange);
}

.style-poke {
    --style-color: var(--role-tank);
}

.style-unknown {
    --style-color: var(--text-mute);
}

.profile-confidence {
    margin-left: auto;
    color: var(--text-mute);
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.1em;
}

.profile-confidence.inferred {
    color: var(--ow-orange);
}

.strength-weakness {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
    margin-top: 20px;
}

.note-card {
    padding: 17px;
    border-left: 3px solid var(--note-color);
    background: var(--bg-1);
}

.strengths {
    --note-color: var(--win);
}

.weaknesses {
    --note-color: var(--loss);
}

.note-card h3 {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 12px;
    color: var(--text);
    font-family: var(--font-heading);
    font-size: 0.9rem;
    font-style: italic;
    letter-spacing: 0.06em;
}

.note-mark {
    display: grid;
    width: 21px;
    height: 21px;
    place-items: center;
    border: 1px solid var(--note-color);
    color: var(--note-color);
    font-size: 0.9rem;
}

.note-card ul {
    display: grid;
    gap: 8px;
    margin: 0;
    padding-left: 17px;
    color: var(--text-dim);
    font-size: 0.82rem;
    line-height: 1.55;
}

.counter-section,
.map-section,
.composition-section {
    margin-top: clamp(32px, 5vw, 54px);
}

.section-heading {
    align-items: end;
}

.section-heading h2 {
    font-size: clamp(1.55rem, 3.5vw, 2.25rem);
}

.section-tag {
    border-color: var(--surface-line-strong);
    color: var(--text-mute);
    white-space: nowrap;
}

.tierlist-section {
    margin-bottom: clamp(32px, 5vw, 54px);
    padding: clamp(18px, 3vw, 28px);
}

.tierlist-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
}

.tierlist-controls > label {
    color: var(--text-mute);
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.08em;
}

.tierlist-controls select {
    min-width: 190px;
    padding: 9px 30px 9px 11px;
    border: 1px solid var(--surface-line-strong);
    background: var(--bg-1);
    color: var(--text);
    font: inherit;
    font-size: 0.75rem;
}

.tier-role-filters {
    margin-left: auto;
}

.tierlist-note,
.counter-stats-note {
    margin: 12px 0 16px;
    color: var(--text-mute);
    font-size: 0.7rem;
    line-height: 1.55;
}

.counter-stats-note {
    margin: -5px 0 16px;
}

.tier-groups {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
}

.tier-group {
    min-width: 0;
    border-top: 2px solid var(--tier-color);
    background: var(--bg-1);
}

.tier-s {
    --tier-color: var(--win);
}

.tier-a {
    --tier-color: var(--ow-orange);
}

.tier-b {
    --tier-color: var(--role-tank);
}

.tier-c {
    --tier-color: var(--role-damage);
}

.tier-d {
    --tier-color: var(--text-mute);
}

.tier-group h3 {
    margin: 0;
    padding: 10px 12px;
    border-bottom: 1px solid var(--surface-line);
    color: var(--tier-color);
    font-family: var(--font-heading);
    font-size: 1.1rem;
    font-style: italic;
}

.tier-group ol {
    display: grid;
    gap: 2px;
    margin: 0;
    padding: 5px;
    list-style: none;
}

.tier-group button {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--text);
    cursor: pointer;
    text-align: left;
}

.tier-group button:hover,
.tier-group button.selected {
    border-color: var(--tier-color);
    background: var(--bg-2);
}

.tier-hero-name,
.tier-hero-stats {
    display: grid;
    gap: 3px;
}

.tier-hero-name {
    font-size: 0.73rem;
    font-weight: 700;
}

.tier-hero-name small,
.tier-hero-stats small {
    color: var(--text-mute);
    font-size: 0.58rem;
}

.tier-hero-stats {
    flex: 0 0 auto;
    text-align: right;
}

.tier-hero-stats strong {
    color: var(--tier-color);
    font-size: 0.76rem;
    font-variant-numeric: tabular-nums;
}

.tierlist-empty {
    margin: 0;
    color: var(--text-dim);
    font-size: 0.78rem;
}

.counter-intro,
.cycle-intro {
    max-width: 82ch;
    margin: 11px 0 17px;
    color: var(--text-dim);
    font-size: 0.82rem;
    line-height: 1.6;
}

.counter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 13px;
}

.counter-card {
    padding: 17px;
    border-top: 2px solid var(--role-color);
}

.counter-card > header {
    display: flex;
    align-items: center;
    gap: 9px;
    padding-bottom: 13px;
    border-bottom: 1px solid var(--surface-line);
}

.role-mark {
    display: grid;
    width: 28px;
    height: 28px;
    flex: 0 0 auto;
    place-items: center;
    border: 1px solid color-mix(in srgb, var(--role-color) 45%, transparent);
    color: var(--role-color);
}

.counter-card h3 {
    margin: 0;
    color: var(--text);
    font-family: var(--font-heading);
    font-size: 1rem;
    font-style: italic;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.counter-count {
    margin-left: auto;
    color: var(--text-mute);
    font-family: var(--font-heading);
    font-size: 1rem;
    font-variant-numeric: tabular-nums;
}

.counter-card ol {
    display: grid;
    gap: 14px;
    margin: 0;
    padding: 15px 0 0;
    list-style: none;
}

.counter-pick {
    display: grid;
    grid-template-columns: 28px minmax(0, 1fr);
    gap: 9px;
}

.pick-number {
    color: var(--role-color);
    font-family: var(--font-heading);
    font-size: 0.85rem;
    font-variant-numeric: tabular-nums;
    font-weight: 800;
}

.counter-pick strong {
    display: block;
    margin-bottom: 4px;
    color: var(--text);
    font-size: 0.81rem;
}

.counter-pick p,
.counter-empty {
    margin: 0;
    color: var(--text-dim);
    font-size: 0.73rem;
    line-height: 1.5;
}

.counter-pick .counter-pick-stats {
    margin-top: 5px;
    color: var(--ow-orange);
    font-size: 0.67rem;
    font-weight: 700;
}

.counter-empty {
    padding-top: 15px;
}

.data-notice {
    margin: 12px 0 0;
    color: var(--text-mute);
    font-size: 0.69rem;
    line-height: 1.5;
}

.data-notice a {
    margin-left: 4px;
    color: var(--text-dim);
    text-underline-offset: 3px;
}

.map-intro {
    max-width: 90ch;
    margin: 12px 0 18px;
    color: var(--text-dim);
    font-size: 0.8rem;
    line-height: 1.6;
}

.map-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.map-set {
    padding: 18px;
    border-top: 2px solid var(--map-color);
}

.map-set.favorable {
    --map-color: var(--win);
}

.map-set.difficult {
    --map-color: var(--loss);
}

.map-set h3 {
    margin: 0;
    color: var(--map-color);
    font-family: var(--font-heading);
    font-size: 1rem;
    font-style: italic;
    letter-spacing: 0.06em;
}

.map-set ol {
    display: grid;
    gap: 14px;
    margin: 15px 0 0;
    padding: 0;
    list-style: none;
}

.map-set li {
    padding-top: 12px;
    border-top: 1px solid var(--surface-line);
}

.map-set li:first-child {
    padding-top: 0;
    border-top: 0;
}

.map-set li strong {
    display: block;
    margin-bottom: 4px;
    color: var(--text);
    font-size: 0.81rem;
}

.map-set li p,
.map-empty,
.map-error {
    margin: 0;
    color: var(--text-dim);
    font-size: 0.75rem;
    line-height: 1.55;
}

.map-empty {
    margin-top: 14px;
}

.map-error {
    margin-bottom: 14px;
    padding: 10px 12px;
    border-left: 2px solid var(--loss);
    background: var(--bg-1);
}

.counterwatch-external {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin-top: 15px;
    color: var(--ow-orange);
    font-size: 0.72rem;
    font-weight: 800;
    text-decoration: none;
    text-underline-offset: 3px;
}

.counterwatch-external:hover {
    text-decoration: underline;
}

.counterwatch-external svg {
    width: 14px;
    height: 14px;
}

.composition-section {
    padding: clamp(20px, 3vw, 30px);
}

.cycle-intro {
    margin-top: 12px;
}

.cycle-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.cycle-card {
    padding: 16px;
    border: 1px solid var(--surface-line);
    border-top: 2px solid var(--style-color);
    background: var(--bg-1);
}

.cycle-card.style-poke {
    --style-color: var(--role-tank);
}

.cycle-card.style-brawl {
    --style-color: var(--ow-orange);
}

.cycle-card.style-dive {
    --style-color: var(--role-damage);
}

.cycle-card-heading {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cycle-index {
    color: var(--style-color);
    font-family: var(--font-heading);
    font-size: 0.8rem;
    font-weight: 900;
}

.cycle-card h3 {
    margin: 0;
    color: var(--style-color);
    font-family: var(--font-heading);
    font-size: 1.1rem;
    font-style: italic;
    text-transform: uppercase;
}

.cycle-card > p {
    min-height: 62px;
    margin: 11px 0;
    color: var(--text-dim);
    font-size: 0.76rem;
    line-height: 1.5;
}

.beats {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    padding-top: 10px;
    border-top: 1px solid var(--surface-line);
}

.beats svg {
    width: 14px;
    height: 14px;
    color: var(--ow-orange);
}

.beats span {
    color: var(--text-mute);
    font-size: 0.57rem;
    font-weight: 800;
    letter-spacing: 0.08em;
}

.beats strong {
    width: 100%;
    padding-left: 20px;
    color: var(--text);
    font-size: 0.72rem;
}

.cycle-caveat {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin: 15px 0 0;
    color: var(--text-mute);
    font-size: 0.72rem;
    line-height: 1.5;
}

.cycle-caveat svg {
    width: 15px;
    height: 15px;
    flex: 0 0 auto;
    color: var(--ow-orange);
}

.coach-callout {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 18px;
    padding: 18px;
    border: 1px solid var(--surface-line);
    border-left: 3px solid var(--ow-orange);
    background: var(--bg-2);
}

.coach-icon {
    display: grid;
    width: 40px;
    height: 40px;
    flex: 0 0 auto;
    place-items: center;
    background: color-mix(in srgb, var(--ow-orange) 13%, var(--bg-1));
    color: var(--ow-orange);
}

.coach-icon svg {
    width: 20px;
    height: 20px;
}

.coach-callout > div {
    flex: 1;
}

.coach-callout > div > p:last-child {
    margin: 5px 0 0;
    color: var(--text-dim);
    font-size: 0.76rem;
    line-height: 1.5;
}

.coach-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 12px;
    background: var(--ow-orange);
    color: var(--bg-0);
    font-size: 0.64rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-decoration: none;
    white-space: nowrap;
    transition: transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.coach-link:hover {
    transform: translateY(-2px);
}

.coach-link svg {
    width: 14px;
    height: 14px;
}

.catalog-error {
    display: grid;
    gap: 4px;
    margin-bottom: 20px;
    padding: 14px 16px;
    border-left: 3px solid var(--loss);
    background: color-mix(in srgb, var(--loss) 9%, var(--bg-1));
    color: var(--text-dim);
    font-size: 0.78rem;
}

.catalog-error strong {
    color: var(--text);
    font-size: 0.82rem;
}

@media (max-width: 900px) {
    .page-hero {
        grid-template-columns: 1fr;
        align-items: start;
        gap: 22px;
    }

    .search-panel {
        max-width: none;
    }

    .counter-grid {
        grid-template-columns: 1fr;
    }

    .tier-groups {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .counterwatch-content {
        padding: 22px 14px 34px;
    }

    .strength-weakness,
    .cycle-grid,
    .map-columns {
        grid-template-columns: 1fr;
    }

    .section-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .section-tag {
        white-space: normal;
    }

    .tierlist-controls {
        align-items: flex-start;
        flex-direction: column;
    }

    .tier-role-filters {
        margin-left: 0;
    }

    .tier-groups {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .cycle-card > p {
        min-height: 0;
    }

    .coach-callout {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .coach-callout > div {
        flex-basis: calc(100% - 56px);
    }

    .coach-link {
        margin-left: 55px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
