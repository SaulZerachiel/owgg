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
                <span class="simulation-note">SIMULATION // NO PAYMENT REQUIRED</span>
            </header>

            <section class="pricing-cards">
                <article class="pricing-card">
                    <p class="card-kicker">RECRUIT</p>
                    <h2>FREE</h2>
                    <p class="card-description">Pour découvrir le centre de performance.</p>
                    <ul>
                        <li>Profil Battle.net</li>
                        <li>Statistiques Overfast</li>
                        <li>10 analyses IA / jour</li>
                    </ul>
                    <button class="pricing-button secondary">CURRENT PLAN</button>
                </article>

                <article class="pricing-card featured">
                    <span class="featured-label">RECOMMENDED</span>
                    <p class="card-kicker">TACTICIAN</p>
                    <h2>9<span>€</span><small>/ MONTH</small></h2>
                    <p class="card-description">Pour transformer chaque partie en expérience.</p>
                    <ul>
                        <li>Analyses IA avancées</li>
                        <li>Historique de matchs étendu</li>
                        <li>Recommandations personnalisées</li>
                    </ul>
                    <button class="pricing-button primary">COMING SOON</button>
                </article>

                <article class="pricing-card">
                    <p class="card-kicker">COMMANDER</p>
                    <h2>19<span>€</span><small>/ MONTH</small></h2>
                    <p class="card-description">Pour les joueurs qui veulent aller plus loin.</p>
                    <ul>
                        <li>Analyses sans limite</li>
                        <li>Rapports de progression</li>
                        <li>Priorité au coach IA</li>
                    </ul>
                    <button class="pricing-button secondary">COMING SOON</button>
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
    --orange: #f06432;
    --blue: #a9e8ff;
    position: relative;
    min-height: calc(100dvh - 76px);
    overflow: hidden;
    background:
        radial-gradient(circle at 50% 20%, #18384b 0, #0d1b27 24%, transparent 58%),
        linear-gradient(125deg, #091019 0%, #111d29 58%, #0a1119 100%);
    color: #eef6f8;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

.pricing-grid {
    position: absolute;
    inset: 0;
    opacity: 0.14;
    background-image:
        linear-gradient(#8fd9e915 1px, transparent 1px),
        linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 46px 46px;
    transform: perspective(500px) rotateX(55deg) scale(1.5) translateY(28%);
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
    color: var(--orange);
    font-size: 0.66rem;
    letter-spacing: 0.18em;
}

.eyebrow {
    margin-bottom: 14px;
}

h1 {
    margin: 0;
    font-size: clamp(2.5rem, 6vw, 5rem);
    font-weight: 900;
    letter-spacing: -0.07em;
    line-height: 0.9;
}

h1 strong {
    color: transparent;
    -webkit-text-stroke: 1px var(--blue);
}

.pricing-heading > p:not(.eyebrow) {
    margin: 22px auto 14px;
    color: #a7bec7;
    font-size: 0.95rem;
}

.simulation-note {
    color: #7fd8ae;
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
    border: 1px solid #a9e8ff2b;
    background: #09151fcc;
    box-shadow: 0 0 30px #07101866;
}

.pricing-card.featured {
    border-color: var(--orange);
    box-shadow: 0 0 28px #f0643230;
    transform: translateY(-10px);
}

.featured-label {
    position: absolute;
    top: -1px;
    right: 18px;
    padding: 7px 10px;
    background: var(--orange);
    color: #17171a;
    font-size: 0.56rem;
    font-weight: 900;
    letter-spacing: 0.12em;
}

.card-kicker {
    margin: 0 0 18px;
}

.pricing-card h2 {
    margin: 0;
    color: var(--blue);
    font-size: 3.2rem;
    letter-spacing: -0.04em;
}

.featured h2 {
    color: var(--orange);
}

.pricing-card h2 span {
    font-size: 1.4rem;
}

.pricing-card h2 small {
    color: #88a6b2;
    font-size: 0.58rem;
    letter-spacing: 0.12em;
}

.card-description {
    min-height: 44px;
    margin: 12px 0 20px;
    color: #a7bec7;
    font-size: 0.86rem;
    line-height: 1.5;
}

ul {
    display: grid;
    gap: 11px;
    margin: 0 0 24px;
    padding: 0;
    color: #d7e5e8;
    font-size: 0.78rem;
    list-style: none;
}

li::before {
    margin-right: 9px;
    color: var(--orange);
    content: '◆';
    font-size: 0.5rem;
}

.pricing-button {
    width: 100%;
    margin-top: auto;
    padding: 12px;
    border: 1px solid #a9e8ff55;
    background: transparent;
    color: var(--blue);
    font-size: 0.64rem;
    font-weight: 900;
    letter-spacing: 0.14em;
}

.pricing-button.primary {
    border-color: var(--orange);
    background: var(--orange);
    color: #17171a;
}

.pricing-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    color: #88a6b2;
    font-size: 0.58rem;
}

.pricing-footer i {
    display: inline-block;
    width: 6px;
    height: 6px;
    margin-right: 6px;
    border-radius: 50%;
    background: #7fd8ae;
    box-shadow: 0 0 8px #7fd8ae;
}

@media (max-width: 800px) {
    .pricing-cards {
        grid-template-columns: 1fr;
    }

    .pricing-card.featured {
        transform: none;
    }

    .pricing-footer {
        gap: 12px;
        flex-direction: column;
    }
}
</style>
