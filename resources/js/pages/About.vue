<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="About Us" />

    <main class="owgg-about">
        <div class="about-glow about-glow-left" aria-hidden="true"></div>
        <div class="about-glow about-glow-right" aria-hidden="true"></div>
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
                    <div class="flow-icon">◈</div>
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
                    <div class="flow-icon">✦</div>
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
                    <div class="flow-icon">△</div>
                    <p class="flow-kicker">TACTICAL CORE</p>
                    <h2>AI COACH</h2>
                    <p>
                        Notre coach IA transforme ces données en conseils
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
                    <p><span>◆</span> Données compréhensibles, pas de jargon inutile</p>
                    <p><span>◆</span> Une interface pensée pour les joueurs</p>
                    <p><span>◆</span> Des recommandations orientées action</p>
                </div>
            </section>
        </section>
    </main>
</template>

<style scoped>
.owgg-about {
    --orange: #f06432;
    --blue: #a9e8ff;
    position: relative;
    min-height: calc(100dvh - 76px);
    overflow: hidden;
    background:
        radial-gradient(circle at 78% 30%, #18384b 0, #0d1b27 22%, transparent 50%),
        linear-gradient(125deg, #091019 0%, #111d29 58%, #0a1119 100%);
    color: #eef6f8;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

.about-glow {
    position: absolute;
    width: 35vw;
    height: 35vw;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.13;
}

.about-glow-left {
    bottom: -25%;
    left: -18%;
    background: var(--orange);
}

.about-glow-right {
    top: -20%;
    right: -15%;
    background: var(--blue);
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
    color: var(--orange);
    font-size: 0.66rem;
    letter-spacing: 0.18em;
}

.eyebrow {
    margin-bottom: 14px;
}

h1 {
    margin: 0;
    font-size: clamp(3rem, 7vw, 6rem);
    font-weight: 900;
    letter-spacing: -0.08em;
    line-height: 0.85;
}

h1 strong,
.about-values strong {
    color: transparent;
    -webkit-text-stroke: 1px var(--blue);
}

.about-heading > p:last-child {
    max-width: 570px;
    margin: 24px 0 0;
    color: #a7bec7;
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
    border: 1px solid #a9e8ff2b;
    background: #09151fcc;
}

.flow-card:nth-of-type(3) {
    border-color: #f0643266;
}

.flow-number {
    position: absolute;
    top: 18px;
    right: 20px;
    color: #88a6b2;
    font-size: 0.65rem;
    letter-spacing: 0.12em;
}

.flow-icon {
    margin-bottom: 26px;
    color: var(--orange);
    font-size: 2rem;
}

.flow-kicker {
    margin-bottom: 10px;
}

.flow-card h2 {
    margin: 0 0 12px;
    color: var(--blue);
    font-size: 1.3rem;
    letter-spacing: 0.04em;
}

.flow-card p:last-child {
    margin: 0;
    color: #a7bec7;
    font-size: 0.86rem;
    line-height: 1.55;
}

.flow-line {
    width: 28px;
    height: 1px;
    background: var(--orange);
    box-shadow: 0 0 10px var(--orange);
}

.about-values {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    margin-top: 36px;
    padding: 24px 28px;
    border-left: 3px solid var(--orange);
    background: #09151f99;
}

.about-values h2 {
    margin: 0;
    font-size: clamp(1.6rem, 3vw, 2.5rem);
    letter-spacing: -0.04em;
    line-height: 0.9;
}

.value-list {
    color: #a7bec7;
    font-size: 0.8rem;
    line-height: 1.4;
}

.value-list p {
    margin: 8px 0;
}

.value-list span {
    margin-right: 8px;
    color: var(--orange);
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
</style>
