<template>
    <footer class="owgg-legal-footer dark">
        <p>
            Site de fans non officiel. Overwatch® est une marque de Blizzard
            Entertainment, Inc. Ce site n'est ni affilié ni approuvé par
            Blizzard.
        </p>
    </footer>
</template>

<style scoped>
.owgg-legal-footer {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 18px clamp(18px, 5vw, 56px) 24px;
    border-top: 1px solid var(--surface-line);
    color: var(--muted-foreground);
    font-size: 0.72rem;
    letter-spacing: 0.02em;
    line-height: 1.5;
}

.owgg-legal-footer::before {
    width: 3px;
    min-width: 3px;
    height: 24px;
    background: var(--ow-orange);
    content: '';
}

.owgg-legal-footer p {
    max-width: 90ch;
    margin: 0;
}
</style>
