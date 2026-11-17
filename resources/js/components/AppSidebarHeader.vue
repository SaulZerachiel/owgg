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
    border-color: #a9e8ff1f;
    background:
        linear-gradient(90deg, #0d1b27 0%, #0a1119 72%, #112c3b 100%);
    color: #eef6f8;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

.owgg-topbar::after {
    position: absolute;
    inset: 0;
    pointer-events: none;
    content: '';
    opacity: 0.1;
    background-image: linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 32px 32px;
}

.owgg-topbar > * {
    position: relative;
    z-index: 1;
}

.owgg-topbar-label {
    margin-left: auto;
    color: #88a6b2;
    font-size: 0.58rem;
    letter-spacing: 0.18em;
}

:deep(.owgg-sidebar-trigger) {
    color: #a9e8ff;
    border: 1px solid #a9e8ff44;
    border-radius: 0;
    transition: background 0.2s, color 0.2s, transform 0.2s;
}

:deep(.owgg-sidebar-trigger:hover) {
    background: #f06432;
    color: #17171a;
    transform: translateX(2px);
}
</style>
