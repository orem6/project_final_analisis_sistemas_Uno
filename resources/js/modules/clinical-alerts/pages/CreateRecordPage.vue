<template>
    <section class="create">
        <h1 class="create__title">Nuevo Registro Clínico</h1>

        <form class="create__form" @submit.prevent="handleSubmit">
            <label class="create__label">
                Paciente
                <select v-model="form.patient_id" class="create__input" required>
                    <option value="" disabled>Seleccionar paciente</option>
                    <option v-for="patient in store.patients" :key="patient.id" :value="patient.id">
                        {{ patient.name }} {{ patient.last_name }} — {{ patient.document_number }}
                    </option>
                </select>
            </label>

            <label class="create__label">
                Parámetro clínico
                <select v-model="form.record_type" class="create__input" required @change="onTypeChange">
                    <option value="" disabled>Seleccionar parámetro</option>
                    <option v-for="range in store.ranges" :key="range.record_type" :value="range.record_type">
                        {{ range.label }}
                    </option>
                </select>
            </label>

            <label class="create__label">
                Valor
                <input
                    v-model="form.value"
                    class="create__input"
                    type="number"
                    step="0.01"
                    required
                    :placeholder="'Ingrese el valor en ' + selectedUnit"
                >
            </label>

            <label class="create__label">
                Notas (opcional)
                <textarea
                    v-model="form.notes"
                    class="create__input create__textarea"
                    rows="3"
                    placeholder="Observaciones adicionales"
                />
            </label>

            <p v-if="errorMessage" class="create__error">
                {{ errorMessage }}
            </p>

            <p v-if="resultSeverity" class="create__result" :class="`create__result--${resultSeverity}`">
                Resultado: {{ resultSeverityText }} — {{ resultValue }}
            </p>

            <div class="create__actions">
                <router-link class="create__cancel" to="/clinical-alerts">
                    Cancelar
                </router-link>
                <button class="create__submit" type="submit" :disabled="loading">
                    {{ loading ? 'Guardando…' : 'Guardar registro' }}
                </button>
            </div>
        </form>

        <ToastNotification
            :show="toast.show"
            :message="toast.message"
            :type="toast.type"
            @close="toast.show = false"
        />
    </section>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useClinicalAlertsStore } from '@/modules/clinical-alerts/stores/clinicalAlerts';
import ToastNotification from '@/modules/clinical-alerts/components/ToastNotification.vue';

const router = useRouter();
const store = useClinicalAlertsStore();

const form = ref({
    patient_id: '',
    record_type: '',
    value: '',
    notes: '',
    unit: '',
});

const loading = ref(false);
const errorMessage = ref('');
const resultSeverity = ref('');
const resultValue = ref('');

const toast = reactive({
    show: false,
    message: '',
    type: 'info',
});

const selectedUnit = computed(() => {
    const range = store.ranges.find((r) => r.record_type === form.value.record_type);
    return range?.unit ?? '';
});

const resultSeverityText = computed(() => {
    const map = {
        normal: 'Normal',
        warning_low: 'Alerta baja (amarillo)',
        warning_high: 'Alerta alta (naranja)',
        critical: 'Crítico (rojo)',
    };
    return map[resultSeverity.value] ?? '';
});

function onTypeChange() {
    const range = store.ranges.find((r) => r.record_type === form.value.record_type);
    if (range) {
        form.value.unit = range.unit;
    }
    resultSeverity.value = '';
    resultValue.value = '';
}

async function handleSubmit() {
    errorMessage.value = '';
    resultSeverity.value = '';
    loading.value = true;

    try {
        const record = await store.createRecord({
            patient_id: form.value.patient_id,
            record_type: form.value.record_type,
            value: form.value.value,
            unit: form.value.unit,
            notes: form.value.notes,
        });

        resultSeverity.value = record.severity;
        const range = store.ranges.find((r) => r.record_type === record.record_type);
        resultValue.value = `${record.value} ${record.unit}`;

        if (record.severity === 'critical') {
            toast.message = '🚨 ALERTA CRÍTICA: El valor ingresado requiere atención inmediata.';
            toast.type = 'critical';
            toast.show = true;
        } else if (record.severity !== 'normal') {
            toast.message = `⚠ Alerta: ${resultSeverityText.value} — ${resultValue.value}`;
            toast.type = 'warning';
            toast.show = true;
        } else {
            toast.message = '✓ Valor dentro de rango normal.';
            toast.type = 'success';
            toast.show = true;
        }

        form.value.value = '';
        form.value.notes = '';

        await store.fetchAlertSummary();
    } catch (error) {
        errorMessage.value = error?.response?.data?.message
            ?? error?.response?.data?.errors?.[Object.keys(error?.response?.data?.errors ?? {})[0]]?.[0]
            ?? 'Error al guardar el registro.';
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await Promise.all([
        store.fetchPatients(),
        store.fetchRanges(),
    ]);
});
</script>

<style scoped>
.create {
    max-width: 600px;
    margin: 0 auto;
}

.create__title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.create__form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
}

.create__label {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    font-size: 0.9rem;
    color: #334155;
}

.create__input {
    border: 1px solid #cbd5f5;
    border-radius: 8px;
    padding: 0.65rem 0.75rem;
    font-size: 1rem;
}

.create__textarea {
    resize: vertical;
    font-family: inherit;
}

.create__error {
    color: #b91c1c;
    font-size: 0.9rem;
}

.create__result {
    padding: 0.75rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
}

.create__result--normal {
    background: #dcfce7;
    color: #166534;
}

.create__result--warning_low {
    background: #fef9c3;
    color: #854d0e;
}

.create__result--warning_high {
    background: #fed7aa;
    color: #9a3412;
}

.create__result--critical {
    background: #fecaca;
    color: #991b1b;
}

.create__actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    margin-top: 0.5rem;
}

.create__cancel {
    padding: 0.65rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    color: #475569;
    font-weight: 500;
}

.create__submit {
    padding: 0.65rem 1.5rem;
    background: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.create__submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
