<template>
    <div v-if="visible" class="toast" :class="`toast--${type}`">
        <span class="toast__icon">{{ icon }}</span>
        <span class="toast__message">{{ message }}</span>
        <button class="toast__close" @click="close">&times;</button>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    message: { type: String, default: '' },
    type: { type: String, default: 'info' },
    duration: { type: Number, default: 5000 },
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const visible = ref(false);
let timer = null;

const icon = computed(() => {
    const icons = {
        info: 'ℹ',
        success: '✓',
        warning: '⚠',
        critical: '🚨',
    };
    return icons[props.type] ?? 'ℹ';
});

watch(() => props.show, (val) => {
    if (val) {
        visible.value = true;
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => {
            close();
        }, props.duration);
    }
});

function close() {
    visible.value = false;
    if (timer) clearTimeout(timer);
    emit('close');
}
</script>

<style scoped>
.toast {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    font-weight: 500;
    font-size: 0.9rem;
    animation: slideIn 0.3s ease-out;
    max-width: 420px;
}

.toast--info {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

.toast--success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.toast--warning {
    background: #fefce8;
    color: #854d0e;
    border: 1px solid #fef08a;
}

.toast--critical {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.toast__icon {
    font-size: 1.25rem;
}

.toast__message {
    flex: 1;
}

.toast__close {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.6;
}

.toast__close:hover {
    opacity: 1;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
