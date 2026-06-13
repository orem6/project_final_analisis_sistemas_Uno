<template>
    <span
        class="badge"
        :class="[`badge--${severity}`]"
        :title="tooltipText"
    >
        {{ displayValue }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: { type: [Number, String], required: true },
    unit: { type: String, default: '' },
    severity: { type: String, default: 'normal' },
    label: { type: String, default: '' },
    range: { type: Object, default: null },
});

const displayValue = computed(() => {
    return `${props.value}${props.unit ? ' ' + props.unit : ''}`;
});

const severityLabel = computed(() => {
    const labels = {
        normal: 'Normal',
        warning_low: 'Alerta baja',
        warning_high: 'Alerta alta',
        critical: 'Crítico',
    };
    return labels[props.severity] ?? 'Desconocido';
});

const tooltipText = computed(() => {
    let text = `${props.label}: ${displayValue.value}`;
    text += ` | ${severityLabel.value}`;
    if (props.range) {
        text += ` | Rango normal: ${props.range.min_value_normal}-${props.range.max_value_normal} ${props.range.unit}`;
    }
    return text;
});
</script>

<style scoped>
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: default;
    transition: all 0.2s;
}

.badge--normal {
    background: #dcfce7;
    color: #166534;
}

.badge--warning_low {
    background: #fef9c3;
    color: #854d0e;
}

.badge--warning_high {
    background: #fed7aa;
    color: #9a3412;
}

.badge--critical {
    background: #fecaca;
    color: #991b1b;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
