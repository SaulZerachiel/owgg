<template>
    <div class="owgg-logo-mark" aria-hidden="true">
        <span></span>
    </div>
    <div class="owgg-logo-copy">
        <span>OWGG</span>
        <small>PERFORMANCE LAB</small>
    </div>
</template>

<style scoped>
.owgg-logo-mark {
    display: grid;
    width: 34px;
    height: 34px;
    flex: 0 0 auto;
    place-items: center;
}

.owgg-logo-mark span {
    position: relative;
    width: 17px;
    height: 17px;
    border: 2px solid var(--ow-orange);
    transform: rotate(45deg);
}

.owgg-logo-mark span::after {
    position: absolute;
    inset: 3px;
    background: var(--ow-orange);
    content: '';
}

.owgg-logo-copy {
    display: grid;
    margin-left: 4px;
    text-align: left;
    line-height: 1;
}

.owgg-logo-copy span {
    color: var(--text);
    font-family: var(--font-heading);
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 0.12em;
}

.owgg-logo-copy small {
    margin-top: 5px;
    color: var(--text-dim);
    font-size: 0.46rem;
    letter-spacing: 0.16em;
}
</style>
