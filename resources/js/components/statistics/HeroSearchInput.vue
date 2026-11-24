<script setup lang="ts">
defineProps<{
    id: string;
    label: string;
    modelValue: string;
    heroes: string[];
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

function updateValue(event: Event) {
    const input = event.currentTarget;

    if (input instanceof HTMLInputElement) {
        emit('update:modelValue', input.value);
    }
}
</script>

<template>
    <div class="hero-search">
        <label :for="id">{{ label }}</label>
        <input
            :id="id"
            :value="modelValue"
            type="search"
            :list="`${id}-suggestions`"
            :placeholder="placeholder ?? 'Type a hero name…'"
            autocomplete="off"
            @input="updateValue"
        />
        <datalist :id="`${id}-suggestions`">
            <option v-for="hero in heroes" :key="hero" :value="hero" />
        </datalist>
    </div>
</template>

<style scoped>
.hero-search {
    display: grid;
    gap: 7px;
    width: min(100%, 340px);
}

.hero-search label {
    color: var(--text-dim);
    font-family: var(--font-heading);
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.hero-search input {
    min-width: 0;
    padding: 11px 13px;
    border: 1px solid var(--surface-line-strong);
    border-bottom: 2px solid var(--ow-orange);
    background: var(--bg-0);
    color: var(--text);
    font: inherit;
    clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 0 100%);
}

.hero-search input::placeholder {
    color: var(--text-mute);
}

.hero-search input:focus-visible {
    outline: 2px solid var(--ow-orange);
    outline-offset: 3px;
}
</style>
