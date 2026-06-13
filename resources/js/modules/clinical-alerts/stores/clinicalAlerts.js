import { defineStore } from 'pinia';
import api from '@/plugins/axios';

export const useClinicalAlertsStore = defineStore('clinicalAlerts', {
    state: () => ({
        patients: [],
        records: [],
        activeAlerts: [],
        alertSummary: { critical: 0, warning_high: 0, warning_low: 0, total: 0 },
        ranges: [],
        loading: false,
        pagination: null,
    }),

    actions: {
        async fetchPatients() {
            const { data } = await api.get('/patients');
            this.patients = data.data;
        },

        async createPatient(patientData) {
            const { data } = await api.post('/patients', patientData);
            this.patients.push(data.data);
            return data.data;
        },

        async fetchRecords(params = {}) {
            this.loading = true;
            try {
                const { data } = await api.get('/clinical-records', { params });
                this.records = data.data ?? data;
                this.pagination = data.meta ?? null;
            } finally {
                this.loading = false;
            }
        },

        async createRecord(recordData) {
            const { data } = await api.post('/clinical-records', recordData);
            this.records.unshift(data.data);
            return data.data;
        },

        async fetchAlertSummary() {
            const { data } = await api.get('/clinical-records/alerts/summary');
            this.alertSummary = data.data;
        },

        async fetchActiveAlerts() {
            const { data } = await api.get('/clinical-records/alerts/active');
            this.activeAlerts = data.data;
        },

        async fetchRanges() {
            const { data } = await api.get('/clinical-ranges');
            this.ranges = data.data;
        },
    },
});
