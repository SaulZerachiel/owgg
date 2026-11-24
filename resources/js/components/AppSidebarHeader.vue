<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="owgg-topbar flex h-16 shrink-0 items-center gap-2 border-b px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="owgg-sidebar-trigger -ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <span class="owgg-topbar-label">OWGG // COMMAND CENTER</span>
    </header>
</template>

<style scoped>
.owgg-topbar {
    position: relative;
    overflow: hidden;
    border-color: var(--surface-line);
    background: var(--bg-1);
    color: var(--text);
    font-family: var(--font-heading);
}

.owgg-topbar::after {
    position: absolute;
    inset: 0;
    pointer-events: none;
    content: '';
    opacity: 0.05;
    background-image: repeating-linear-gradient(
        135deg,
        transparent 0 14px,
        var(--surface-line) 14px 15px
    );
}

.owgg-topbar > * {
    position: relative;
    z-index: 1;
}

.owgg-topbar-label {
    margin-left: auto;
    color: var(--text-dim);
    font-size: 0.58rem;
    letter-spacing: 0.18em;
}

:deep(.owgg-sidebar-trigger) {
    color: var(--role-tank);
    border: 1px solid var(--surface-line-strong);
    border-radius: 0;
    transition:
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

:deep(.owgg-sidebar-trigger:hover) {
    background: var(--ow-orange);
    color: var(--bg-0);
    transform: translateX(2px);
}
</style>
