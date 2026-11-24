<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <span
        role="status"
        aria-label="Loading"
        :class="cn('owgg-loader', props.class)"
    ></span>
</template>

<style scoped>
.owgg-loader {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    flex: 0 0 auto;
    border: 2px solid var(--surface-line-strong);
    border-top-color: var(--ow-orange);
    border-right-color: var(--ow-orange);
    clip-path: polygon(25% 0, 75% 0, 100% 50%, 75% 100%, 25% 100%, 0 50%);
    animation: owgg-loader-rotate 700ms linear infinite;
}

@keyframes owgg-loader-rotate {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .owgg-loader {
        animation: none;
    }
}
</style>
