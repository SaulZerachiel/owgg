<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<{
    auth: {
        user: {
            name: string;
            avatar?: string | null;
            battletag?: string | null;
        };
    };
}>();
const auth = computed(() => page.props.auth);
const { isCurrentUrl } = useCurrentUrl();

const navItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Coach Winston', href: '/chat' },
        { title: 'Pricing', href: '/pricing' },
        { title: 'About Us', href: '/about-us' },
    ];
    const battleTag = auth.value.user.battletag;

    if (battleTag) {
        items.splice(1, 0, {
            title: 'Statistics',
            href: `/${encodeURIComponent(battleTag)}/stats`,
        });
    }

    return items;
});
</script>

<template>
    <header class="owgg-header">
        <div class="owgg-header-inner">
            <Sheet>
                <SheetTrigger as-child>
                    <Button class="owgg-mobile-trigger lg:hidden" size="icon">
                        <Menu class="size-5" />
                        <span class="sr-only">Ouvrir le menu</span>
                    </Button>
                </SheetTrigger>
                <SheetContent
                    side="left"
                    class="owgg-mobile-menu w-[300px] p-6"
                >
                    <SheetHeader>
                        <SheetTitle class="sr-only">Navigation OWGG</SheetTitle>
                        <AppLogo />
                    </SheetHeader>
                    <nav class="mt-8 flex flex-col gap-2">
                        <Link
                            v-for="item in navItems"
                            :key="item.title"
                            :href="item.href"
                            class="owgg-mobile-link"
                            :class="{ 'is-active': isCurrentUrl(item.href) }"
                        >
                            {{ item.title }}
                        </Link>
                    </nav>
                </SheetContent>
            </Sheet>

            <Link :href="dashboard()" class="owgg-header-logo">
                <AppLogo />
            </Link>

            <nav class="owgg-nav hidden lg:flex">
                <Link
                    v-for="item in navItems"
                    :key="item.title"
                    :href="item.href"
                    class="owgg-nav-link"
                    :class="{ 'is-active': isCurrentUrl(item.href) }"
                >
                    {{ item.title }}
                </Link>
            </nav>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button class="owgg-profile-trigger" variant="ghost">
                        <Avatar
                            class="owgg-profile-avatar size-8 overflow-hidden rounded-full"
                        >
                            <AvatarImage
                                v-if="auth.user.avatar"
                                :src="auth.user.avatar"
                                :alt="auth.user.name"
                            />
                            <AvatarFallback class="owgg-avatar-fallback">
                                {{ getInitials(auth.user?.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <span class="owgg-profile-name hidden sm:inline">
                            {{ auth.user.name }}
                        </span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <UserMenuContent :user="auth.user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="owgg-breadcrumbs">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </div>
    </header>
</template>

<style scoped>
.owgg-header {
    position: relative;
    z-index: 20;
    border-bottom: 1px solid
        color-mix(in srgb, var(--owgg-cyan) 12%, transparent);
    background: linear-gradient(90deg, #0d1b27 0%, #0a1119 72%, #112c3b 100%);
    color: #eef6f8;
    font-family: 'Arial Narrow', 'Trebuchet MS', sans-serif;
}

.owgg-header::after {
    position: absolute;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    content: '';
    opacity: 0.1;
    background-image:
        linear-gradient(#8fd9e915 1px, transparent 1px),
        linear-gradient(90deg, #8fd9e915 1px, transparent 1px);
    background-size: 32px 32px;
}

.owgg-header-inner {
    display: flex;
    width: 100%;
    min-height: 76px;
    align-items: center;
    gap: 36px;
    margin-inline: auto;
    padding-inline: 24px;
}

.owgg-header-logo {
    flex: 0 0 auto;
}

.owgg-nav {
    height: 76px;
    align-items: stretch;
    gap: 8px;
}

.owgg-nav-link,
.owgg-mobile-link {
    position: relative;
    display: flex;
    align-items: center;
    color: #a7bec7;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.11em;
    text-decoration: none;
    text-transform: uppercase;
    transition:
        color 0.2s,
        background 0.2s;
}

.owgg-nav-link {
    padding: 0 18px;
}

.owgg-nav-link:hover,
.owgg-nav-link.is-active,
.owgg-mobile-link:hover,
.owgg-mobile-link.is-active {
    color: #eef6f8;
}

.owgg-nav-link.is-active {
    background: color-mix(in srgb, var(--owgg-orange) 10%, transparent);
}

.owgg-nav-link.is-active::after {
    position: absolute;
    right: 12px;
    bottom: 0;
    left: 12px;
    height: 3px;
    background: var(--owgg-orange);
    box-shadow: 0 0 12px var(--owgg-orange);
    content: '';
}

.owgg-profile-trigger {
    min-width: 0;
    margin-left: auto;
    padding: 5px 0 5px 14px;
    border: 0;
    border-left: 1px solid #a9e8ff44;
    border-radius: 0;
    color: #eef6f8;
}

.owgg-profile-trigger:hover {
    background: #18384b;
}

.owgg-profile-name {
    max-width: 150px;
    overflow: hidden;
    color: #a7bec7;
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-overflow: ellipsis;
    text-transform: uppercase;
}

.owgg-profile-avatar {
    border: 1px solid var(--owgg-orange);
    box-shadow:
        0 0 0 3px color-mix(in srgb, var(--owgg-orange) 12%, transparent),
        0 0 14px color-mix(in srgb, var(--owgg-orange) 22%, transparent);
}

.owgg-avatar-fallback {
    background: var(--owgg-orange);
    color: #17171a;
    font-weight: 900;
}

.owgg-mobile-trigger {
    border: 1px solid #a9e8ff44;
    border-radius: 0;
    background: transparent;
    color: #a9e8ff;
}

.owgg-mobile-menu {
    background: #0a1119;
    color: #eef6f8;
}

.owgg-mobile-link {
    padding: 14px;
    border-left: 2px solid transparent;
}

.owgg-mobile-link.is-active {
    border-left-color: var(--owgg-orange);
    background: color-mix(in srgb, var(--owgg-orange) 10%, transparent);
}

.owgg-breadcrumbs {
    display: flex;
    width: min(1280px, calc(100% - 40px));
    min-height: 40px;
    align-items: center;
    margin-inline: auto;
    border-top: 1px solid #a9e8ff12;
    color: #88a6b2;
}

@media (max-width: 640px) {
    .owgg-header-inner {
        width: 100%;
        min-height: 64px;
        gap: 14px;
        padding-inline: 12px;
    }

    .owgg-header-logo :deep(.owgg-logo-copy) {
        display: none;
    }
}
</style>
