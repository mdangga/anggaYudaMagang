<template>
    <transition name="fade">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/70" @click="closeModal" />

            <!-- Modal -->
            <transition name="scale">
                <div class="relative w-full max-w-md mx-4 rounded-2xl
                           bg-white dark:bg-gray-800 p-6
                           shadow-xl">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ title }}
                        </h3>

                        <button @click="closeModal" :disabled="loading" class="text-gray-400 hover:text-gray-500
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="space-y-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ message }}
                        </p>

                        <div v-if="warningMessage" class="flex gap-2 p-3 rounded-lg
                                   bg-yellow-50 dark:bg-yellow-900/20">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>

                            <p class="text-sm text-yellow-700 dark:text-yellow-500">
                                {{ warningMessage }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeModal" :disabled="loading" class="inline-flex items-center justify-center rounded-md
                                   border border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-700 px-4 py-2 text-sm
                                   text-gray-700 dark:text-gray-300
                                   hover:bg-gray-50 dark:hover:bg-gray-600
                                   disabled:opacity-50 disabled:cursor-not-allowed">
                            Batal
                        </button>

                        <button type="button" @click="confirm" :disabled="loading" class="relative inline-flex items-center justify-center
                                   rounded-md px-4 py-2 text-sm font-medium
                                   bg-danger hover:bg-danger-600 text-neutral-50 hover:text-neutral-100
                                   disabled:opacity-70 disabled:cursor-not-allowed
                                   transition-all overflow-hidden">
                            <span class="flex items-center gap-2">
                                <svg v-if="loading" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0
                                           C5.373 0 0 5.373 0 12h4z" />
                                </svg>

                                {{ loading ? 'Memproses...' : confirmText }}
                            </span>
                        </button>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>

<script setup>
const props = defineProps({
    show: { type: Boolean, required: true },
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, required: true },
    warningMessage: { type: String, default: '' },
    confirmText: { type: String, default: 'Hapus' },
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['close', 'confirm'])

const closeModal = () => {
    if (!props.loading) emit('close')
}

const confirm = () => {
    if (!props.loading) emit('confirm')
}
</script>

<style scoped>
/* Fade backdrop */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Scale modal */
.scale-enter-active,
.scale-leave-active {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
    transform: scale(0.95);
    opacity: 0;
}
</style>
