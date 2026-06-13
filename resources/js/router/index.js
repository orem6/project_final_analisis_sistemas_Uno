import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '@/pages/HomePage.vue';
import LoginPage from '@/modules/auth/pages/LoginPage.vue';
import ClinicalAlertsPage from '@/modules/clinical-alerts/pages/ClinicalAlertsPage.vue';
import CreateRecordPage from '@/modules/clinical-alerts/pages/CreateRecordPage.vue';
import PatientDetailPage from '@/modules/clinical-alerts/pages/PatientDetailPage.vue';
import { authGuard } from '@/router/guards';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: HomePage,
        },
        {
            path: '/login',
            name: 'login',
            component: LoginPage,
            meta: { guest: true },
        },
        {
            path: '/clinical-alerts',
            name: 'clinicalAlerts',
            component: ClinicalAlertsPage,
            meta: { requiresAuth: true },
        },
        {
            path: '/clinical-alerts/create',
            name: 'clinicalAlertsCreate',
            component: CreateRecordPage,
            meta: { requiresAuth: true },
        },
        {
            path: '/clinical-alerts/patient/:id',
            name: 'patientDetail',
            component: PatientDetailPage,
            meta: { requiresAuth: true },
        },
    ],
});

router.beforeEach(authGuard);

export default router;
