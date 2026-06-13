<template>
    <section class="detail">
        <header class="detail__header">
            <router-link class="detail__back" to="/clinical-alerts">
                &larr; Volver a alertas
            </router-link>
            <h1 class="detail__title">{{ patient?.name }} {{ patient?.last_name }}</h1>
            <p class="detail__meta">
                {{ patient?.document_number }} &middot;
                {{ patient?.gender === 'male' ? 'Masculino' : patient?.gender === 'female' ? 'Femenino' : 'No especificado' }}
            </p>
        </header>

        <AlertSummaryCard :summary="summary" />

        <div class="detail__filters">
            <select v-model="filters.record_type" class="detail__filter">
                <option value="">Todos los parámetros</option>
                <option v-for="range in store.ranges" :key="range.record_type" :value="range.record_type">
                    {{ range.label }}
                </option>
            </select>
            <button class="detail__filter-btn" @click="applyFilters">
                Filtrar
            </button>
        </div>

        <div v-if="loading" class="detail__loading">Cargando historial...</div>

        <div v-else-if="records.length === 0" class="detail__empty">
            No hay registros clínicos para este paciente.
        </div>

        <div v-else class="detail__timeline">
            <div
                v-for="record in records"
                :key="record.id"
                class="detail__item"
                :class="`detail__item--${record.severity}`"
            >
                <div class="detail__item-header">
                    <ClinicalValueBadge
                        :value="record.value"
                        :unit="record.unit"
                        :severity="record.severity"
                        :label="getRangeLabel(record.record_type)"
                        :range="getRange(record.record_type)"
                    />
                    <span class="detail__item-type">{{ getRangeLabel(record.record_type) }}</span>
                </div>
                <div class="detail__item-body">
                    <p v-if="record.notes" class="detail__item-notes">{{ record.notes }}</p>
                </div>
                <div class="detail__item-footer">
                    <span class="detail__item-date">{{ formatDate(record.recorded_at) }}</span>
                    <span class="detail__item-severity">{{ severityText(record.severity) }}</span>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { useRoute } from 'vue-router';
import { useClinicalAlertsStore } from '@/modules/clinical-alerts/stores/clinicalAlerts';
import ClinicalValueBadge from '@/modules/clinical-alerts/components/ClinicalValueBadge.vue';
import AlertSummaryCard from '@/modules/clinical-alerts/components/AlertSummaryCard.vue';
import api from '@/plugins/axios';

const route = useRoute();
const store = useClinicalAlertsStore();

const patient = ref(null);
const records = ref([]);
const summary = ref({ critical: 0, warning_high: 0, warning_low: 0, total: 0 });
const loading = ref(false);

const filters = reactive({
    record_type: '',
});

const patientId = computed(() => route.params.id);

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

async function loadData() {
    loading.value = true;
    try {
        const params = { patient_id: patientId.value };
        if (filters.record_type) params.record_type = filters.record_type;

        const { data } = await api.get('/clinical-records', { params });
        records.value = data.data ?? data;

        const alertRecords = records.value.filter(
            (r) => r.severity !== 'normal'
        );
        summary.value = {
            critical: alertRecords.filter((r) => r.severity === 'critical').length,
            warning_high: alertRecords.filter((r) => r.severity === 'warning_high').length,
            warning_low: alertRecords.filter((r) => r.severity === 'warning_low').length,
            total: alertRecords.length,
        };

        if (records.value.length > 0) {
            patient.value = records.value[0].patient;
        }
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    loadData();
}

onMounted(async () => {
    await store.fetchRanges();
    await loadData();
});
</script>

<style scoped>
.detail__header {
    margin-bottom: 1.5rem;
}

.detail__back {
    display: inline-block;
    margin-bottom: 0.5rem;
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
}

.detail__title {
    font-size: 1.5rem;
    font-weight: 700;
}

.detail__meta {
    color: #64748b;
    font-size: 0.9rem;
}

.detail__filters {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.detail__filter {
    padding: 0.5rem 0.75rem;
    border: 1px solid #cbd5f5;
    border-radius: 8px;
    font-size: 0.9rem;
    background: #ffffff;
}

.detail__filter-btn {
    padding: 0.5rem 1rem;
    background: #475569;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}

.detail__loading,
.detail__empty {
    padding: 2rem;
    text-align: center;
    color: #64748b;
}

.detail__timeline {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.detail__item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    border-left: 4px solid #e2e8f0;
}

.detail__item--critical { border-left-color: #dc2626; }
.detail__item--warning_high { border-left-color: #ea580c; }
.detail__item--warning_low { border-left-color: #ca8a04; }
.detail__item--normal { border-left-color: #16a34a; }

.detail__item-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.detail__item-type {
    font-weight: 600;
    font-size: 0.9rem;
}

.detail__item-notes {
    color: #475569;
    font-size: 0.85rem;
    margin: 0;
}

.detail__item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #94a3b8;
}
</style>
