<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div class="appearance-tabs" role="group" aria-label="Appearance">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :aria-pressed="appearance === value"
            :class="{ 'is-active': appearance === value }"
        >
            <component :is="Icon" aria-hidden="true" />
            <span>{{ label }}</span>
        </button>
    </div>
</template>

<style scoped>
.appearance-tabs {
    display: inline-flex;
    gap: 4px;
    padding: 4px;
    border: 1px solid var(--border);
    background: var(--muted);
}

.appearance-tabs button {
    display: inline-flex;
    min-height: 38px;
    align-items: center;
    gap: 7px;
    padding: 8px 13px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--muted-foreground);
    cursor: pointer;
    font-family: var(--font-heading);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition:
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.appearance-tabs button:hover {
    color: var(--foreground);
}

.appearance-tabs button.is-active {
    background: var(--ow-orange);
    color: var(--bg-0);
}

.appearance-tabs button:active {
    transform: scale(0.97);
}

.appearance-tabs button:focus-visible {
    outline: 2px solid var(--ow-orange);
    outline-offset: 3px;
}

.appearance-tabs svg {
    width: 16px;
    height: 16px;
}

@media (max-width: 420px) {
    .appearance-tabs {
        display: grid;
        width: 100%;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .appearance-tabs button {
        justify-content: center;
        padding-inline: 8px;
    }
}
</style>
