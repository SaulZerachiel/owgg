<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Pricing" />

    <main class="owgg-pricing">
        <div class="pricing-grid" aria-hidden="true"></div>
        <div class="pricing-content">
            <header class="pricing-heading">
                <p class="eyebrow">OWGG // ACCESS LEVELS</p>
                <h1>CHOOSE YOUR <strong>LOADOUT.</strong></h1>
                <p>
                    Des offres simples pour analyser vos performances et
                    progresser plus intelligemment.
                </p>
                <span class="simulation-note"
                    >SIMULATION // NO PAYMENT REQUIRED</span
                >
            </header>

            <section class="pricing-cards">
                <article class="pricing-card">
                    <p class="card-kicker">RECRUIT</p>
                    <h2>FREE</h2>
                    <p class="card-description">
                        Pour découvrir le centre de performance.
                    </p>
                    <ul>
                        <li>Profil Battle.net</li>
                        <li>Statistiques Overfast</li>
                        <li>10 analyses de Coach Winston / jour</li>
                    </ul>
                    <button class="pricing-button secondary">
                        CURRENT PLAN
                    </button>
                </article>

                <article class="pricing-card featured">
                    <span class="featured-label">RECOMMENDED</span>
                    <p class="card-kicker">TACTICIAN</p>
                    <h2>9<span>€</span><small>/ MONTH</small></h2>
                    <p class="card-description">
                        Pour transformer chaque partie en expérience.
                    </p>
                    <ul>
                        <li>Analyses avancées de Coach Winston</li>
                        <li>Historique de matchs étendu</li>
                        <li>Recommandations personnalisées</li>
                    </ul>
                    <button class="pricing-button primary">COMING SOON</button>
                </article>

                <article class="pricing-card">
                    <p class="card-kicker">COMMANDER</p>
                    <h2>19<span>€</span><small>/ MONTH</small></h2>
                    <p class="card-description">
                        Pour les joueurs qui veulent aller plus loin.
                    </p>
                    <ul>
                        <li>Analyses sans limite</li>
                        <li>Rapports de progression</li>
                        <li>Priorité à Coach Winston</li>
                    </ul>
                    <button class="pricing-button secondary">
                        COMING SOON
                    </button>
                </article>
            </section>

            <footer class="pricing-footer">
                <span>ALL SYSTEMS ARE CURRENTLY IN DEVELOPMENT</span>
                <span><i></i> NO COMMITMENT</span>
            </footer>
        </div>
    </main>
</template>

<style scoped>
.owgg-pricing {
    --orange: var(--ow-orange);
    --blue: var(--role-tank);
    position: relative;
    min-height: calc(100dvh - 76px);
    overflow: hidden;
    background:
        radial-gradient(ellipse at 50% 5%, var(--bg-3) 0, transparent 48%),
        var(--bg-0);
    color: var(--text);
}

.pricing-grid {
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

.pricing-content {
    position: relative;
    z-index: 1;
    width: min(1180px, calc(100% - 48px));
    margin-inline: auto;
    padding: clamp(32px, 7vh, 70px) 0 24px;
}

.pricing-heading {
    max-width: 700px;
    margin: 0 auto 34px;
    text-align: center;
}

.eyebrow,
.card-kicker,
.simulation-note,
.pricing-footer {
    color: var(--ow-orange-text);
    font-size: 0.66rem;
    letter-spacing: 0.18em;
}

.eyebrow {
    margin-bottom: 14px;
    font-weight: 800;
}

h1 {
    margin: 0;
    font-size: clamp(2.5rem, 6vw, 5rem);
    font-weight: 900;
    letter-spacing: -0.07em;
    line-height: 0.9;
    font-style: italic;
    text-transform: uppercase;
}

h1 strong {
    color: var(--ow-orange-text);
}

.pricing-heading > p:not(.eyebrow) {
    margin: 22px auto 14px;
    color: var(--text-dim);
    font-size: 0.95rem;
}

.simulation-note {
    color: var(--win);
    font-size: 0.58rem;
}

.pricing-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.pricing-card {
    position: relative;
    display: flex;
    min-height: 350px;
    flex-direction: column;
    padding: 28px;
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
    transition:
        border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.pricing-card:hover {
    border-color: color-mix(in srgb, var(--ow-orange) 48%, transparent);
    transform: translateY(-4px);
}

.pricing-card.featured {
    border-color: var(--orange);
    background: linear-gradient(
        145deg,
        color-mix(in srgb, var(--ow-orange) 8%, var(--bg-2)),
        var(--bg-2)
    );
    transform: translateY(-10px);
}

.featured-label {
    position: absolute;
    top: -1px;
    right: 18px;
    padding: 7px 10px;
    background: var(--orange);
    color: var(--text-on-accent);
    font-size: 0.56rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    clip-path: polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 0 100%);
}

.card-kicker {
    margin: 0 0 18px;
}

.pricing-card h2 {
    margin: 0;
    color: var(--text);
    font-size: 3.2rem;
    letter-spacing: -0.04em;
    font-variant-numeric: tabular-nums;
    font-style: italic;
}

.featured h2 {
    color: var(--ow-orange-text);
}

.pricing-card h2 span {
    font-size: 1.4rem;
}

.pricing-card h2 small {
    color: var(--text-dim);
    font-size: 0.58rem;
    letter-spacing: 0.12em;
}

.card-description {
    min-height: 44px;
    margin: 12px 0 20px;
    color: var(--text-dim);
    font-size: 0.86rem;
    line-height: 1.5;
}

ul {
    display: grid;
    gap: 11px;
    margin: 0 0 24px;
    padding: 0;
    color: var(--text);
    font-size: 0.78rem;
    list-style: none;
}

li::before {
    margin-right: 9px;
    color: var(--ow-orange-text);
    content: '▸';
    font-size: 0.5rem;
}

.pricing-button {
    width: 100%;
    margin-top: auto;
    padding: 12px;
    border: 1px solid var(--surface-line-strong);
    background: transparent;
    color: var(--text-dim);
    font-size: 0.64rem;
    font-weight: 900;
    letter-spacing: 0.14em;
    font-family: var(--font-heading);
    clip-path: polygon(
        0 0,
        calc(100% - 9px) 0,
        100% 9px,
        100% 100%,
        9px 100%,
        0 calc(100% - 9px)
    );
    transition:
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.pricing-button.primary {
    border-color: var(--orange);
    background: var(--orange);
    color: var(--text-on-accent);
}

.pricing-button:hover {
    border-color: var(--orange);
    color: var(--ow-orange-text);
    transform: translateY(-1px);
}

.pricing-button.primary:hover {
    color: var(--text-on-accent);
    box-shadow: 0 0 20px var(--ow-orange-glow);
}

.pricing-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    color: var(--text-dim);
    font-size: 0.58rem;
}

.pricing-footer i {
    display: inline-block;
    width: 6px;
    height: 6px;
    margin-right: 6px;
    background: var(--win);
}

@media (max-width: 800px) {
    .pricing-cards {
        grid-template-columns: 1fr;
    }

    .pricing-card.featured {
        transform: none;
    }

    .pricing-card:hover {
        transform: none;
    }

    .pricing-footer {
        gap: 12px;
        flex-direction: column;
    }
}

@media (prefers-reduced-motion: reduce) {
    .pricing-card,
    .pricing-button {
        transition: none;
    }
}
</style>
