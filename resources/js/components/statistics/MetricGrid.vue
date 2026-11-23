<script setup lang="ts">
import { computed } from 'vue';

type MetricValue = number | string | boolean | null;

const props = defineProps<{
    metrics: Record<string, MetricValue>;
}>();

const rows = computed(() =>
    Object.entries(props.metrics).map(([key, value]) => ({
        key,
        label: key
            .replaceAll('_', ' ')
            .replace(/\b\w/g, (letter) => letter.toUpperCase()),
        value: formatValue(key, value),
    })),
);

function formatValue(key: string, value: MetricValue): string {
    if (value === null || value === '') {
        return '—';
    }

    const numericValue = typeof value === 'number' ? value : Number(value);

    if (key === 'time_played' && Number.isFinite(numericValue)) {
        const hours = Math.floor(numericValue / 3600);
        const minutes = Math.floor((numericValue % 3600) / 60);

        return hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
    }

    if (
        typeof value === 'number' ||
        (typeof value === 'string' &&
            value.trim() !== '' &&
            Number.isFinite(numericValue))
    ) {
        const formatted = new Intl.NumberFormat(undefined, {
            maximumFractionDigits: 2,
        }).format(numericValue);

        return key.includes('winrate') || key.includes('win_percentage')
            ? `${formatted}%`
            : formatted;
    }

    return String(value);
}
</script>

<template>
    <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        <div
            v-for="row in rows"
            :key="row.key"
            class="min-w-0 border border-[#a9e8ff1f] bg-[#0a1119]/70 px-4 py-3"
        >
            <dt class="text-xs tracking-[0.12em] text-[#88a6b2] uppercase">
                {{ row.label }}
            </dt>
            <dd class="mt-2 text-base font-semibold break-words text-[#a9e8ff]">
                {{ row.value }}
            </dd>
        </div>
    </dl>
</template>
