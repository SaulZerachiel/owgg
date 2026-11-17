<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, LayoutGrid, MessageCircle } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Coach IA',
        href: '/chat',
        icon: MessageCircle,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/SaulZerachiel/owgg',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://github.com/SaulZerachiel/owgg#readme',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar class="owgg-sidebar" collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

<style scoped>
:deep(.owgg-sidebar) {
    --sidebar-background: #0a1119;
    --sidebar-foreground: #b6c8ce;
    --sidebar-primary: #f06432;
    --sidebar-primary-foreground: #17171a;
    --sidebar-accent: #182b38;
    --sidebar-accent-foreground: #eef6f8;
    --sidebar-border: #a9e8ff1f;
    background:
        radial-gradient(circle at 20% 12%, #18384b 0, transparent 34%),
        linear-gradient(180deg, #0d1b27 0%, #091019 70%);
    border-right-color: #a9e8ff1f;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

:deep(.owgg-sidebar [data-sidebar='sidebar']) {
    background: transparent;
}

:deep(.owgg-sidebar [data-sidebar='sidebar']::after) {
    position: absolute;
    inset: 0;
    pointer-events: none;
    content: '';
    opacity: 0.1;
    background-image:
        linear-gradient(#8fd9e915 1px, transparent 1px),
        linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 28px 28px;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] > *) {
    position: relative;
    z-index: 1;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='menu-button']) {
    color: #a7bec7;
    border-radius: 0;
    letter-spacing: 0.04em;
    transition: background 0.2s, color 0.2s, transform 0.2s;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='menu-button']:hover) {
    background: #18384b;
    color: #eef6f8;
    transform: translateX(3px);
}

:deep(
    .owgg-sidebar
        [data-sidebar='sidebar']
        [data-sidebar='menu-button'][data-active='true']
) {
    background: #f06432;
    color: #17171a;
    box-shadow: 4px 0 0 #a9e8ff;
    font-weight: 800;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='group-label']) {
    color: #f06432;
    font-size: 0.58rem;
    letter-spacing: 0.18em;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='menu']) {
    gap: 7px;
}
</style>
