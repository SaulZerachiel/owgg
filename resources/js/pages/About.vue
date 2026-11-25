<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="About Us" />

    <main class="owgg-about">
        <div class="about-pattern" aria-hidden="true"></div>
        <section class="about-content">
            <header class="about-heading">
                <p class="eyebrow">OWGG // MISSION BRIEF</p>
                <h1>READ THE <strong>GAME.</strong></h1>
                <p>
                    OWGG transforme vos données de jeu en décisions claires,
                    pour vous aider à progresser partie après partie.
                </p>
            </header>

            <section class="about-flow">
                <article class="flow-card">
                    <span class="flow-number">01</span>
                    <div
                        class="flow-icon flow-icon-identity"
                        aria-hidden="true"
                    ></div>
                    <p class="flow-kicker">IDENTITY LINK</p>
                    <h2>BATTLE.NET OAUTH</h2>
                    <p>
                        Connectez votre compte Blizzard de manière sécurisée
                        grâce à l’authentification OAuth.
                    </p>
                </article>
                <div class="flow-line" aria-hidden="true"></div>
                <article class="flow-card">
                    <span class="flow-number">02</span>
                    <div
                        class="flow-icon flow-icon-data"
                        aria-hidden="true"
                    ></div>
                    <p class="flow-kicker">DATA STREAM</p>
                    <h2>OVERFAST API</h2>
                    <p>
                        Vos profils, héros et matchs sont récupérés pour
                        construire une vue complète de vos performances.
                    </p>
                </article>
                <div class="flow-line" aria-hidden="true"></div>
                <article class="flow-card">
                    <span class="flow-number">03</span>
                    <div
                        class="flow-icon flow-icon-coach"
                        aria-hidden="true"
                    ></div>
                    <p class="flow-kicker">TACTICAL CORE</p>
                    <h2>COACH WINSTON</h2>
                    <p>
                        Coach Winston transforme ces données en conseils
                        concrets, lisibles et adaptés à votre style de jeu.
                    </p>
                </article>
            </section>

            <section class="about-values">
                <div>
                    <p class="eyebrow">OUR PROTOCOL</p>
                    <h2>LESS NOISE.<br /><strong>MORE IMPACT.</strong></h2>
                </div>
                <div class="value-list">
                    <p>
                        <span>◆</span> Données compréhensibles, pas de jargon
                        inutile
                    </p>
                    <p><span>◆</span> Une interface pensée pour les joueurs</p>
                    <p><span>◆</span> Des recommandations orientées action</p>
                </div>
            </section>
        </section>
    </main>
</template>

<style scoped>
.owgg-about {
    --orange: var(--ow-orange);
    --blue: var(--role-tank);
    position: relative;
    min-height: calc(100dvh - 76px);
    overflow: hidden;
    background:
        radial-gradient(ellipse at 82% 18%, var(--bg-3) 0, transparent 46%),
        var(--bg-0);
    color: var(--text);
}

.about-pattern {
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

.about-content {
    position: relative;
    z-index: 1;
    width: min(1180px, calc(100% - 48px));
    margin-inline: auto;
    padding: clamp(32px, 7vh, 70px) 0;
}

.about-heading {
    max-width: 760px;
    margin-bottom: 48px;
}

.eyebrow,
.flow-kicker {
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
    font-size: clamp(3rem, 7vw, 6rem);
    font-weight: 900;
    letter-spacing: -0.08em;
    line-height: 0.85;
    font-style: italic;
    text-transform: uppercase;
}

h1 strong,
.about-values strong {
    color: var(--ow-orange-text);
}

.about-heading > p:last-child {
    max-width: 570px;
    margin: 24px 0 0;
    color: var(--text-dim);
    font-size: 1rem;
    line-height: 1.6;
}

.about-flow {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 16px;
}

.flow-card {
    position: relative;
    min-height: 260px;
    padding: 26px;
    border: 1px solid var(--surface-line-strong);
    background: var(--bg-2);
    clip-path: polygon(
        0 0,
        calc(100% - 16px) 0,
        100% 16px,
        100% 100%,
        16px 100%,
        0 calc(100% - 16px)
    );
    transition:
        border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.flow-card:hover {
    border-color: color-mix(in srgb, var(--ow-orange) 58%, transparent);
    transform: translateY(-3px);
}

.flow-card:nth-of-type(3) {
    border-color: color-mix(in srgb, var(--ow-orange) 52%, transparent);
}

.flow-number {
    position: absolute;
    top: 18px;
    right: 20px;
    color: var(--text-mute);
    font-family: var(--font-heading);
    font-weight: 800;
    font-size: 0.65rem;
    letter-spacing: 0.12em;
}

.flow-icon {
    position: relative;
    width: 40px;
    height: 40px;
    margin-bottom: 26px;
    border: 1px solid var(--icon-color, var(--ow-orange));
    background: color-mix(
        in srgb,
        var(--icon-color, var(--ow-orange)) 12%,
        transparent
    );
    clip-path: polygon(25% 0, 75% 0, 100% 50%, 75% 100%, 25% 100%, 0 50%);
}

.flow-icon::after {
    position: absolute;
    inset: 10px;
    border: 1px solid var(--icon-color, var(--ow-orange));
    content: '';
    transform: rotate(45deg);
}

.flow-icon-identity {
    --icon-color: var(--role-tank);
}

.flow-icon-data {
    --icon-color: var(--ow-orange-text);
}

.flow-icon-coach {
    --icon-color: var(--role-support);
}

.flow-kicker {
    margin-bottom: 10px;
}

.flow-card h2 {
    margin: 0 0 12px;
    color: var(--text);
    font-size: 1.3rem;
    letter-spacing: 0.04em;
    font-style: italic;
}

.flow-card p:last-child {
    margin: 0;
    color: var(--text-dim);
    font-size: 0.86rem;
    line-height: 1.55;
}

.flow-line {
    width: 28px;
    height: 1px;
    background: var(--orange);
}

.about-values {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    margin-top: 36px;
    padding: 24px 28px;
    border: 1px solid var(--surface-line);
    border-left: 3px solid var(--orange);
    background: var(--bg-1);
}

.about-values h2 {
    margin: 0;
    font-size: clamp(1.6rem, 3vw, 2.5rem);
    letter-spacing: -0.04em;
    line-height: 0.9;
}

.value-list {
    color: var(--text-dim);
    font-size: 0.8rem;
    line-height: 1.4;
}

.value-list p {
    margin: 8px 0;
}

.value-list span {
    margin-right: 8px;
    color: var(--ow-orange-text);
    font-size: 0.55rem;
}

@media (max-width: 850px) {
    .about-flow {
        grid-template-columns: 1fr;
    }

    .flow-line {
        width: 1px;
        height: 22px;
        margin-inline: auto;
    }

    .about-values {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (prefers-reduced-motion: reduce) {
    .flow-card {
        transition: none;
    }

    .flow-card:hover {
        transform: none;
    }
}
</style>
