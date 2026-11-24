<template>
    <div class="owgg-logo-mark">
        <span>✦</span>
    </div>
    <div class="owgg-logo-copy">
        <span>OWGG</span>
        <small>PERFORMANCE LAB</small>
    </div>
</template>

<style scoped>
.owgg-logo-mark {
    display: grid;
    width: 32px;
    height: 32px;
    flex: 0 0 auto;
    place-items: center;
    border: 1px solid var(--owgg-orange);
    color: var(--owgg-orange);
    font-size: 1.15rem;
    clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
}

.owgg-logo-copy {
    display: grid;
    margin-left: 4px;
    text-align: left;
    line-height: 1;
}

.owgg-logo-copy span {
    color: #eef6f8;
    font-size: 0.9rem;
    font-weight: 900;
    letter-spacing: 0.14em;
}

.owgg-logo-copy small {
    margin-top: 5px;
    color: #88a6b2;
    font-size: 0.46rem;
    letter-spacing: 0.12em;
}
</style>
