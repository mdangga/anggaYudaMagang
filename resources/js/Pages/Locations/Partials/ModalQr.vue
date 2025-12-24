<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                        QR Code Form Request Lokasi
                    </h3>
                    <button @click="close"
                        class="text-neutral-400 dark:text-neutral-300 hover:text-neutral-500 dark:hover:text-neutral-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- Loading State -->
                <div v-if="loading" class="flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-neutral-500 dark:text-neutral-300">Membuat QR Code...</p>
                </div>

                <!-- Success State -->
                <div v-else-if="qrData" class="space-y-4">
                    <div class="flex justify-center">
                        <div>
                            <img :src="qrData.qrCode" alt="QR Code"
                                class="w-64 h-64 border-2 border-gray-200 dark:border-neutral-700 rounded-lg"
                                @error="handleImageError" />

                            <div v-if="imageError"
                                class="w-64 h-64 flex flex-col items-center justify-center bg-gray-100 dark:bg-neutral-700 rounded-lg">
                                <svg class="h-12 w-12 text-neutral-400 mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <p class="text-neutral-500 dark:text-neutral-300 text-center text-sm">
                                    QR Code gagal dimuat<br>
                                    <small>Pastikan koneksi internet stabil</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Link Generate
                        </label>
                        <div class="flex">
                            <input type="text" :value="qrData.link" readonly
                                class="flex-1 border border-gray-300 dark:border-neutral-700 rounded-l-md px-3 py-2 text-sm bg-gray-50 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-50">
                            <button @click="copyLink"
                                class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 text-sm transition-colors">
                                Salin
                            </button>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Link ini akan kadaluarsa sesuai waktu yang ditentukan
                        </p>
                    </div>

                    <div v-if="qrData?.link" class="mt-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Bagikan Link
                        </label>

                        <div class="flex flex-wrap gap-2">
                            <button @click="shareViaWhatsApp"
                                class="flex items-center gap-2 px-3 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M17.6 6.32A7.85 7.85 0 0 0 12 4a7.94 7.94 0 0 0-6.88 11.89L4 20l4.2-1.1a7.9 7.9 0 0 0 3.79 1a8 8 0 0 0 8-7.93a8 8 0 0 0-2.39-5.65M12 18.53a6.6 6.6 0 0 1-3.36-.92l-.24-.15l-2.49.66l.66-2.43l-.16-.25a6.6 6.6 0 0 1 10.25-8.17a6.65 6.65 0 0 1 2 4.66a6.66 6.66 0 0 1-6.66 6.6m3.61-4.94c-.2-.1-1.17-.58-1.35-.64s-.32-.1-.45.1a9 9 0 0 1-.63.77c-.11.14-.23.15-.43 0a5.33 5.33 0 0 1-2.69-2.35c-.21-.35.2-.33.58-1.08a.38.38 0 0 0 0-.35c0-.1-.45-1.08-.61-1.47s-.32-.33-.45-.34h-.39a.7.7 0 0 0-.53.25A2.2 2.2 0 0 0 8 10.17a3.8 3.8 0 0 0 .81 2.05a8.9 8.9 0 0 0 3.39 3a3.85 3.85 0 0 0 2.38.5a2 2 0 0 0 1.33-.94a1.6 1.6 0 0 0 .12-.94c-.09-.1-.22-.15-.42-.25" />
                                </svg>
                                WhatsApp
                            </button>

                            <button @click="shareViaTelegram"
                                class="flex items-center gap-2 px-3 py-2 bg-sky-500 text-white rounded-lg text-sm hover:bg-sky-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="m16.463 8.846l-1.09 6.979a.588.588 0 0 1-.894.407l-3.65-2.287a.588.588 0 0 1-.095-.923l3.03-2.904c.034-.032-.006-.085-.046-.061l-4.392 2.628a1.23 1.23 0 0 1-.87.153l-1.59-.307c-.574-.111-.653-.899-.114-1.122l8.502-3.515a.882.882 0 0 1 1.21.952" />
                                    <path fill="currentColor" fill-rule="evenodd"
                                        d="M12 1.706C6.315 1.706 1.706 6.315 1.706 12S6.315 22.294 12 22.294S22.294 17.685 22.294 12S17.685 1.706 12 1.706M3.47 12a8.53 8.53 0 1 1 17.06 0a8.53 8.53 0 0 1-17.06 0"
                                        clip-rule="evenodd" />
                                </svg>
                                Telegram
                            </button>
                        </div>
                    </div>

                    <!-- Timestamp -->
                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                        Digenerate pada: {{ qrData.timestamp }}
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="py-8 text-center">
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <svg class="h-10 w-10 text-red-500 dark:text-red-400 mx-auto mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <p class="text-red-700 dark:text-red-300 font-medium mb-1">Gagal Generate QR Code</p>
                        <p class="text-red-600 dark:text-red-400 text-sm">
                            {{ error }}
                        </p>
                        <button @click="retryGenerate"
                            class="mt-3 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm transition-colors">
                            Coba Lagi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t flex justify-between">
                <button @click="close"
                    class="px-4 py-2 border border-gray-300 dark:border-neutral-700 rounded-md text-sm text-neutral-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                    Tutup
                </button>
                <button @click="downloadQRCode" :disabled="!qrData?.qrCode || loading"
                    class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Download QR Code
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import QRCode from 'qrcode'
import axios from 'axios'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    apiUrl: {
        type: String,
        default: '/locations/generate-link'
    }
})

const emit = defineEmits(['close', 'generated', 'error'])

// State
const loading = ref(false)
const qrData = ref(null)
const error = ref(null)
const imageError = ref(false)

// Methods
const close = () => {
    emit('close')
}

const handleImageError = () => {
    imageError.value = true
}

const copyLink = async () => {
    if (!qrData.value?.link) return
    
    try {
        await navigator.clipboard.writeText(qrData.value.link)
        emit('generated', { type: 'copy', message: 'Link berhasil disalin' })
    } catch (err) {
        emit('error', { type: 'copy', message: 'Gagal menyalin link', error: err })
    }
}

const downloadQRCode = () => {
    if (!qrData.value?.qrCode) return
    
    try {
        const a = document.createElement('a')
        a.href = qrData.value.qrCode
        a.download = `qr-code-lokasi-${new Date().toISOString().slice(0, 10)}.png`
        a.click()
        emit('generated', { type: 'download', message: 'QR Code berhasil diunduh' })
    } catch (err) {
        emit('error', { type: 'download', message: 'Gagal mengunduh QR Code', error: err })
    }
}

const shareViaWhatsApp = () => {
    if (!qrData.value?.link) return
    
    const encodedLink = encodeURIComponent(qrData.value.link)
    window.open(`https://wa.me/?text=${encodedLink}`, '_blank')
}

const shareViaTelegram = () => {
    if (!qrData.value?.link) return
    
    const encodedLink = encodeURIComponent(qrData.value.link)
    window.open(`https://t.me/share/url?url=${encodedLink}`, '_blank')
}

const retryGenerate = async () => {
    await generateQRCode()
}

// Main function to generate QR Code
const generateQRCode = async () => {
    if (!props.show) return
    
    loading.value = true
    error.value = null
    imageError.value = false
    
    try {
        // Fetch link from API
        const { data } = await axios.post(props.apiUrl)
        
        if (!data?.link) {
            throw new Error('Link tidak ditemukan dalam respons')
        }

        // Generate QR Code
        const qrCode = await QRCode.toDataURL(data.link, {
            width: 200,
            margin: 4,
            errorCorrectionLevel: 'H',
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        })

        // Set QR Data
        qrData.value = {
            link: data.link,
            qrCode,
            timestamp: new Date().toLocaleString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            })
        }

        emit('generated', { type: 'generate', data: qrData.value })
    } catch (err) {
        console.error('QR Code generation error:', err)
        error.value = err.response?.data?.message || err.message || 'Gagal generate QR Code'
        emit('error', { type: 'generate', error: err })
    } finally {
        loading.value = false
    }
}

// Watch for show prop changes
watch(() => props.show, (newVal) => {
    if (newVal && !qrData.value) {
        generateQRCode()
    }
}, { immediate: true })
</script>