<template>
    <div class="summary">
        <div class="summary__card summary__card--critical">
            <span class="summary__count">{{ summary.critical }}</span>
            <span class="summary__label">Críticas</span>
        </div>
        <div class="summary__card summary__card--warning-high">
            <span class="summary__count">{{ summary.warning_high }}</span>
            <span class="summary__label">Alertas altas</span>
        </div>
        <div class="summary__card summary__card--warning-low">
            <span class="summary__count">{{ summary.warning_low }}</span>
            <span class="summary__label">Alertas bajas</span>
        </div>
        <div class="summary__card summary__card--total">
            <span class="summary__count">{{ summary.total }}</span>
            <span class="summary__label">Total alertas</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    summary: {
        type: Object,
        required: true,
    },
});
</script>

<style scoped>
.summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary__card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
}

.summary__count {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.summary__label {
    font-size: 0.8rem;
    margin-top: 0.25rem;
    color: #475569;
}

.summary__card--critical .summary__count { color: #991b1b; }
.summary__card--warning-high .summary__count { color: #9a3412; }
.summary__card--warning-low .summary__count { color: #854d0e; }
.summary__card--total .summary__count { color: #1e40af; }
</style>
