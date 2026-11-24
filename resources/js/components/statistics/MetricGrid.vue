<script setup lang="ts">
import { computed } from 'vue';
import { formatStatLabel } from '@/lib/formatStatLabel';

type MetricValue = number | string | boolean | null;

const props = defineProps<{
    metrics: Record<string, MetricValue>;
}>();

const rows = computed(() =>
    Object.entries(props.metrics).map(([key, value]) => {
        const statLabel = formatStatLabel(key);

        return {
            key,
            label: [statLabel.label, statLabel.qualifier]
                .filter(Boolean)
                .join(' · '),
            value: formatValue(key, value),
        };
    }),
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
    <dl class="metric-grid">
        <div v-for="row in rows" :key="row.key" class="metric-card">
            <dt>
                {{ row.label }}
            </dt>
            <dd>
                {{ row.value }}
            </dd>
        </div>
    </dl>
</template>

<style scoped>
.metric-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.metric-card {
    position: relative;
    min-width: 0;
    padding: 14px 14px 13px 16px;
    border: 1px solid var(--surface-line);
    background: var(--bg-2);
    clip-path: polygon(
        0 0,
        calc(100% - 11px) 0,
        100% 11px,
        100% 100%,
        11px 100%,
        0 calc(100% - 11px)
    );
    transition:
        border-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 150ms cubic-bezier(0.2, 0.8, 0.2, 1),
        background-color 150ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.metric-card::before {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 2px;
    background: var(--ow-orange);
    content: '';
    opacity: 0.75;
}

.metric-card:hover {
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--ow-orange) 48%, transparent);
    background: var(--bg-3);
}

.metric-card dt {
    color: var(--text-dim);
    font-family: var(--font-heading);
    font-size: 0.63rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    line-height: 1.4;
    text-transform: uppercase;
}

.metric-card dd {
    margin: 8px 0 0;
    color: var(--text);
    font-size: clamp(0.9rem, 2vw, 1.1rem);
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    overflow-wrap: anywhere;
}

@media (min-width: 640px) {
    .metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1100px) {
    .metric-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (prefers-reduced-motion: reduce) {
    .metric-card {
        transition: none;
    }

    .metric-card:hover {
        transform: none;
    }
}
</style>
