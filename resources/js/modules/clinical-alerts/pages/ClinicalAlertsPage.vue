<template>
    <section class="alerts">
        <header class="alerts__header">
            <h1 class="alerts__title">Alertas Clínicas</h1>
            <router-link class="alerts__action" to="/clinical-alerts/create">
                + Nuevo registro
            </router-link>
        </header>

        <AlertSummaryCard :summary="store.alertSummary" />

        <div class="alerts__filters">
            <select v-model="filters.severity" class="alerts__filter">
                <option value="">Todas las severidades</option>
                <option value="critical">Crítico</option>
                <option value="warning_high">Alerta alta</option>
                <option value="warning_low">Alerta baja</option>
                <option value="normal">Normal</option>
            </select>

            <select v-model="filters.record_type" class="alerts__filter">
                <option value="">Todos los tipos</option>
                <option v-for="range in store.ranges" :key="range.record_type" :value="range.record_type">
                    {{ range.label }}
                </option>
            </select>

            <input
                v-model="filters.from"
                class="alerts__filter"
                type="date"
                title="Desde"
            >

            <input
                v-model="filters.to"
                class="alerts__filter"
                type="date"
                title="Hasta"
            >

            <button class="alerts__filter-btn" @click="applyFilters">
                Filtrar
            </button>
            <button class="alerts__filter-btn alerts__filter-btn--secondary" @click="clearFilters">
                Limpiar
            </button>
        </div>

        <div v-if="store.loading" class="alerts__loading">
            Cargando...
        </div>

        <div v-else-if="store.records.length === 0" class="alerts__empty">
            No hay registros clínicos aún.
        </div>

        <div v-else class="alerts__table-wrapper">
            <table class="alerts__table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Parámetro</th>
                        <th>Valor</th>
                        <th>Severidad</th>
                        <th>Registrado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="record in store.records"
                        :key="record.id"
                        class="alerts__row"
                        :class="`alerts__row--${record.severity}`"
                    >
                        <td>
                            <router-link
                                class="alerts__patient-link"
                                :to="`/clinical-alerts/patient/${record.patient_id}`"
                            >
                                {{ record.patient?.name }} {{ record.patient?.last_name }}
                            </router-link>
                        </td>
                        <td>{{ getRangeLabel(record.record_type) }}</td>
                        <td>
                            <ClinicalValueBadge
                                :value="record.value"
                                :unit="record.unit"
                                :severity="record.severity"
                                :label="getRangeLabel(record.record_type)"
                                :range="getRange(record.record_type)"
                            />
                        </td>
                        <td>{{ severityText(record.severity) }}</td>
                        <td>{{ formatDate(record.recorded_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive } from 'vue';
import { useClinicalAlertsStore } from '@/modules/clinical-alerts/stores/clinicalAlerts';
import ClinicalValueBadge from '@/modules/clinical-alerts/components/ClinicalValueBadge.vue';
import AlertSummaryCard from '@/modules/clinical-alerts/components/AlertSummaryCard.vue';

const store = useClinicalAlertsStore();

const filters = reactive({
    severity: '',
    record_type: '',
    from: '',
    to: '',
});

function getRangeLabel(recordType) {
    const range = store.ranges.find((r) => r.record_type === recordType);
    return range?.label ?? recordType;
}

function getRange(recordType) {
    return store.ranges.find((r) => r.record_type === recordType) ?? null;
}

function severityText(severity) {
    const map = {
        normal: 'Normal',
        warning_low: 'Alerta baja',
        warning_high: 'Alerta alta',
        critical: 'Crítico',
    };
    return map[severity] ?? severity;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleString('es-MX');
}

function applyFilters() {
    const params = {};
    if (filters.severity) params.severity = filters.severity;
    if (filters.record_type) params.record_type = filters.record_type;
    if (filters.from) params.from = filters.from;
    if (filters.to) params.to = filters.to;
    store.fetchRecords(params);
}

function clearFilters() {
    filters.severity = '';
    filters.record_type = '';
    filters.from = '';
    filters.to = '';
    store.fetchRecords();
}

onMounted(async () => {
    await Promise.all([
        store.fetchRecords(),
        store.fetchAlertSummary(),
        store.fetchRanges(),
    ]);
});
</script>

<style scoped>
.alerts__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.alerts__title {
    font-size: 1.5rem;
    font-weight: 700;
}

.alerts__action {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #2563eb;
    color: #ffffff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.alerts__filters {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.alerts__filter {
    padding: 0.5rem 0.75rem;
    border: 1px solid #cbd5f5;
    border-radius: 8px;
    font-size: 0.9rem;
    background: #ffffff;
}

.alerts__filter-btn {
    padding: 0.5rem 1rem;
    background: #475569;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}

.alerts__filter-btn--secondary {
    background: #e2e8f0;
    color: #475569;
}

.alerts__patient-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.alerts__patient-link:hover {
    text-decoration: underline;
}

.alerts__loading,
.alerts__empty {
    padding: 2rem;
    text-align: center;
    color: #64748b;
}

.alerts__table-wrapper {
    overflow-x: auto;
}

.alerts__table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.alerts__table th {
    text-align: left;
    padding: 0.75rem 1rem;
    background: #f1f5f9;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
}

.alerts__table td {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    border-top: 1px solid #e2e8f0;
}

.alerts__row--critical {
    border-left: 4px solid #dc2626;
}

.alerts__row--warning_high {
    border-left: 4px solid #ea580c;
}

.alerts__row--warning_low {
    border-left: 4px solid #ca8a04;
}

.alerts__row--normal {
    border-left: 4px solid #16a34a;
}
</style>
