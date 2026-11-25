<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    ChartNoAxesCombined,
    FolderGit2,
    LayoutGrid,
    MessageCircle,
} from '@lucide/vue';
import { computed } from 'vue';
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

const page = usePage<{
    auth: { user: { battletag?: string | null } };
}>();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
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
    const battleTag = page.props.auth.user.battletag;

    if (battleTag) {
        items.splice(1, 0, {
            title: 'Statistics',
            href: `/${encodeURIComponent(battleTag)}/stats`,
            icon: ChartNoAxesCombined,
        });
    }

    return items;
});

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
    --sidebar-background: var(--bg-1);
    --sidebar-foreground: var(--text-dim);
    --sidebar-primary: var(--ow-orange);
    --sidebar-primary-foreground: var(--text-on-accent);
    --sidebar-accent: var(--bg-3);
    --sidebar-accent-foreground: var(--text);
    --sidebar-border: var(--surface-line);
    background: var(--bg-1);
    border-right-color: var(--surface-line);
    font-family: var(--font-heading);
}

:deep(.owgg-sidebar [data-sidebar='sidebar']) {
    background: transparent;
}

:deep(.owgg-sidebar [data-sidebar='sidebar']::after) {
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

:deep(.owgg-sidebar [data-sidebar='sidebar'] > *) {
    position: relative;
    z-index: 1;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='menu-button']) {
    color: var(--text-dim);
    border-radius: 0;
    letter-spacing: 0.04em;
    transition:
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

:deep(
    .owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='menu-button']:hover
) {
    background: var(--bg-3);
    color: var(--text);
    transform: translateX(2px);
}

:deep(
    .owgg-sidebar
        [data-sidebar='sidebar']
        [data-sidebar='menu-button'][data-active='true']
) {
    background: var(--ow-orange);
    color: var(--text-on-accent);
    box-shadow: 4px 0 0 var(--role-tank);
    font-weight: 800;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='group-label']) {
    color: var(--ow-orange-text);
    font-size: 0.58rem;
    letter-spacing: 0.18em;
}

:deep(.owgg-sidebar [data-sidebar='sidebar'] [data-sidebar='menu']) {
    gap: 7px;
}
</style>
