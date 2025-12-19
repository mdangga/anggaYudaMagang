<script setup>
import axios from 'axios'
import QRCode from 'qrcode'
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalDelete from '@/Components/ModalDelete.vue'
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    locations: Object
})

// State untuk QR Code
const showQrModal = ref(false)
const qrData = ref(null)
const loading = ref(false)
const error = ref(null)
const showDetailModal = ref(false)
const selectedLocation = ref(null)

const showDeleteModal = ref(false)
const deleting = ref(false)
const itemIdToDelete = ref(null)
const itemName = ref('')

// Function untuk generate QR Code
const generateQrCode = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await axios.post('/locations/generate-link')

        const link = response?.data?.link
        if (!link) {
            throw new Error('Signed link tidak tersedia')
        }

        const qrCodeDataUrl = await QRCode.toDataURL(link, {
            width: 200,
            margin: 4,
            errorCorrectionLevel: 'M'
        })

        qrData.value = {
            link,
            qrCode: qrCodeDataUrl,
            timestamp: new Date().toLocaleString()
        }

        showQrModal.value = true
    } catch (err) {
        console.error('Error generating QR:', err)
        toast.error("Gagal generate QR Code. Silakan coba lagi.", {
            position: "top-right",
            autoClose: 5000,
        });
    } finally {
        loading.value = false
    }
}


// Fungsi untuk download QR Code
const downloadQRCode = () => {
    if (!qrData.value) return

    const downloadLink = document.createElement('a')
    downloadLink.href = qrData.value.qrCode
    downloadLink.download = `qr-code-lokasi-${qrData.value.locationId}.png`
    document.body.appendChild(downloadLink)
    downloadLink.click()
    document.body.removeChild(downloadLink)
}

// Fungsi untuk copy link
const copyLink = () => {
    if (!qrData.value) return

    navigator.clipboard.writeText(qrData.value.link)
        .then(() => {
            toast.success("Link berhasil disalin ke clipboard !", {
                position: "top-right",
                autoClose: 5000,
            });
        })
        .catch(err => {
            console.error('Gagal menyalin link:', err)
            toast.error("Gagal menyalin link !", {
                position: "top-right",
                autoClose: 5000,
            });
        })
}

// Fungsi untuk share via WhatsApp
const shareViaWhatsApp = (link) => {
    const text = `Isi form lokasi magang di sini: ${link}`
    const url = `https://wa.me/?text=${encodeURIComponent(text)}`
    window.open(url, '_blank')
}

// Fungsi untuk share via Telegram
const shareViaTelegram = (link) => {
    const text = `Isi form lokasi magang di sini: ${link}`
    const url = `https://t.me/share/url?url=${encodeURIComponent(link)}&text=${encodeURIComponent(text)}`
    window.open(url, '_blank')
}

const openDetailModal = (location) => {
    selectedLocation.value = location
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
    selectedLocation.value = null
}

const openDeleteModal = (id, name) => {
    itemIdToDelete.value = id
    itemName.value = name
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    if (!deleting.value) {
        showDeleteModal.value = false
        itemIdToDelete.value = null
        itemName.value = ''
    }
}

const selectedLoc = computed(() => {
    if (!itemIdToDelete.value || !props.locations?.data) return null
    return props.locations.data.find(
        location => location.id === itemIdToDelete.value
    )
})

const paginationLinks = computed(() => {
    if (!props.locations || !props.locations.links) {
        return []
    }

    // Jika format dari Laravel pagination
    return props.locations.links.map(link => ({
        url: link.url,
        label: link.label,
        active: link.active
    }))
})

const warningMessage = computed(() => {
    return 'Data yang dihapus tidak dapat dikembalikan.'
})

const deleteMessage = computed(() => {
    return `Apakah Anda yakin ingin menghapus lokasi "${itemName.value}"? Tindakan ini tidak dapat dibatalkan.`
})

const approveRequest = () => {
    router.post(route('locations.approve', selectedLocation.value.id_location), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Lokasi magang telah disetujui !", {
                position: "top-right",
                autoClose: 5000,
            });
            showDetailModal.value = false
            selectedLocation.value = null
        },
        onError: (errors) => {
            console.error('Approval error:', errors)
            toast.error("Gagal menyetujui lokasi magang. Silakan coba lagi !", {
                position: "top-right",
                autoClose: 5000,
            });
        }
    })
}

const deleteItem = () => {
    deleting.value = true

    router.delete(route('locations.destroy', itemIdToDelete.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.success("Lokasi magang berhasil dihapus !", {
                position: "top-right",
                autoClose: 5000,
            });
            showDeleteModal.value = false
            itemIdToDelete.value = null
            itemName.value = ''
        },
        onError: (errors) => {
            console.error('Delete error:', errors)
            toast.error("Gagal menghapus lokasi magang. Silakan coba lagi !", {
                position: "top-right",
                autoClose: 5000,
            });
        },
        onFinish: () => {
            deleting.value = false
        }
    })
}

const handlePageClick = (url) => {
    if (url) {
        router.visit(url)
    }
}
</script>

<template>

    <Head title="Daftar Lokasi Magang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">
                        Daftar Lokasi Magang
                    </h2>
                    <p class="text-neutral-500 dark:text-neutral-300 text-xs mt-1">
                        Kelola lokasi magang
                    </p>
                </div>

                <button @click="generateQrCode()" :disabled="loading"
                    class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-l from-primary-300 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 ease-out overflow-hidden"
                    :class="{ 'opacity-70 cursor-not-allowed': loading }">

                    <!-- Shimmer Effect -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                    </div>

                    <!-- Loading Spinner -->
                    <svg v-if="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>

                    <!-- QR Code Icon -->
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>

                    <!-- Button Text -->
                    <span class="relative text-sm">
                        {{ loading ? 'Memproses...' : 'Generate QR' }}
                    </span>

                    <!-- Tooltip -->
                    <div
                        class="absolute bottom-full mb-2 hidden group-hover:block px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap">
                        Generate QR Code
                        <div
                            class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900">
                        </div>
                    </div>
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="p-4">

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-2 border-gray-200 dark:border-slate-700">
                                <thead class="bg-gray-100 dark:bg-slate-900">
                                    <tr>
                                        <th class="th">No</th>
                                        <th class="th">Mahasiswa</th>
                                        <th class="th">NIM</th>
                                        <th class="th">Lokasi</th>
                                        <th class="th">Kontak</th>
                                        <th class="th">Koordinat</th>
                                        <th class="th">Fakultas</th>
                                        <th class="th">Status</th>
                                        <th class="th text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="(item, index) in locations.data" :key="item.id_location"
                                        class="border-t dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700">
                                        <td class="td">
                                            {{ index + 1 + (locations.current_page - 1) * locations.per_page }}
                                        </td>
                                        <td class="td">
                                            {{ item.student_name }}
                                        </td>
                                        <td class="td">
                                            {{ item.nim }}
                                        </td>
                                        <td class="td">
                                            <div class="font-medium">
                                                {{ item.name_location }}
                                            </div>
                                            <div class="text-sm text-neutral-500 dark:text-slate-400">
                                                {{ item.description }}
                                            </div>
                                        </td>
                                        <td class="td">
                                            {{ item.contact }}
                                        </td>
                                        <td class="td text-sm">
                                            {{ item.latitude }}, {{ item.longitude }}
                                        </td>
                                        <td class="td">
                                            <div class="font-medium">
                                                {{ item.department?.name_department }}
                                                ({{ item.department?.degree_level }})
                                            </div>
                                            <div class="text-sm text-neutral-500 dark:text-slate-400">
                                                {{ item.department?.faculty?.name_faculty }}
                                            </div>
                                        </td>
                                        <td class="td">
                                            <span v-if="item.approved_at"
                                                class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                                Disetujui
                                            </span>
                                            <span v-else
                                                class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200">
                                                Pending
                                            </span>
                                        </td>
                                        <td class="td text-center gap-2 flex justify-center">
                                            <button @click="openDetailModal(item)"
                                                class="group relative inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-info hover:bg-info-600 text-neutral-50 hover:text-neutral-100 font-medium rounded-md shadow hover:shadow-md transition-all duration-300 ease-out overflow-hidden text-sm">
                                                <!-- Document Icon -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path fill="currentColor"
                                                        d="M20 3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-9 14H5v-2h6zm8-4H5v-2h14zm0-4H5V7h14z" />
                                                </svg>

                                                <!-- Button Text -->
                                                <span class="relative">
                                                    Detail
                                                </span>
                                            </button>
                                            <button @click="openDeleteModal(item.id_location, item.name_location)"
                                                class="group relative inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-danger hover:bg-danger-600 text-neutral-50 hover:text-neutral-100 font-medium rounded-md shadow hover:shadow-md transition-all duration-300 ease-out overflow-hidden text-sm">
                                                <!-- Trash Icon -->
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>

                                                <!-- Button Text -->
                                                <span class="relative">
                                                    Hapus
                                                </span>
                                            </button>

                                        </td>
                                    </tr>

                                    <tr v-if="locations.data.length === 0">
                                        <td colspan="9" class="text-center py-6 text-neutral-500 dark:text-neutral-400">
                                            Data belum tersedia
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination v-if="paginationLinks.length > 0" :links="paginationLinks" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal QR Code -->
        <div v-if="showQrModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            QR Code Form Request Lokasi
                        </h3>
                        <button @click="showQrModal = false"
                            class="text-neutral-400 dark:text-neutral-300 hover:text-neutral-500 dark:hover:text-neutral-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div v-if="qrData" class="space-y-4">
                        <div class="flex justify-center">
                            <div>
                                <img :src="qrData.qrCode" alt="QR Code"
                                    class="w-64 h-64 border-2 border-gray-200 dark:border-neutral-700 rounded-lg"
                                    @error="qrData.qrCode = null">

                                <div v-if="!qrData.qrCode"
                                    class="w-64 h-64 flex items-center justify-center bg-gray-100 dark:bg-neutral-700">
                                    <p class="text-neutral-500 dark:text-neutral-300 text-center">
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
                                    class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 text-sm">
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
                                <button @click="shareViaWhatsApp(qrData.link)"
                                    class="flex items-center gap-2 px-2 py-2 bg-green-500 text-white rounded-full text-sm hover:bg-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M17.6 6.32A7.85 7.85 0 0 0 12 4a7.94 7.94 0 0 0-6.88 11.89L4 20l4.2-1.1a7.9 7.9 0 0 0 3.79 1a8 8 0 0 0 8-7.93a8 8 0 0 0-2.39-5.65M12 18.53a6.6 6.6 0 0 1-3.36-.92l-.24-.15l-2.49.66l.66-2.43l-.16-.25a6.6 6.6 0 0 1 10.25-8.17a6.65 6.65 0 0 1 2 4.66a6.66 6.66 0 0 1-6.66 6.6m3.61-4.94c-.2-.1-1.17-.58-1.35-.64s-.32-.1-.45.1a9 9 0 0 1-.63.77c-.11.14-.23.15-.43 0a5.33 5.33 0 0 1-2.69-2.35c-.21-.35.2-.33.58-1.08a.38.38 0 0 0 0-.35c0-.1-.45-1.08-.61-1.47s-.32-.33-.45-.34h-.39a.7.7 0 0 0-.53.25A2.2 2.2 0 0 0 8 10.17a3.8 3.8 0 0 0 .81 2.05a8.9 8.9 0 0 0 3.39 3a3.85 3.85 0 0 0 2.38.5a2 2 0 0 0 1.33-.94a1.6 1.6 0 0 0 .12-.94c-.09-.1-.22-.15-.42-.25" />
                                    </svg>
                                </button>

                                <button @click="shareViaTelegram(qrData.link)"
                                    class="flex items-center gap-2 px-2 py-2 bg-sky-500 text-white rounded-full text-sm hover:bg-sky-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="m16.463 8.846l-1.09 6.979a.588.588 0 0 1-.894.407l-3.65-2.287a.588.588 0 0 1-.095-.923l3.03-2.904c.034-.032-.006-.085-.046-.061l-4.392 2.628a1.23 1.23 0 0 1-.87.153l-1.59-.307c-.574-.111-.653-.899-.114-1.122l8.502-3.515a.882.882 0 0 1 1.21.952" />
                                        <path fill="currentColor" fill-rule="evenodd"
                                            d="M12 1.706C6.315 1.706 1.706 6.315 1.706 12S6.315 22.294 12 22.294S22.294 17.685 22.294 12S17.685 1.706 12 1.706M3.47 12a8.53 8.53 0 1 1 17.06 0a8.53 8.53 0 0 1-17.06 0"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Timestamp -->
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">
                            Digenerate pada: {{ qrData.timestamp }}
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t flex justify-between">
                    <button @click="showQrModal = false"
                        class="px-4 py-2 border border-gray-300 dark:border-neutral-700 rounded-md text-sm text-neutral-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700">
                        Tutup
                    </button>
                    <button @click="downloadQRCode" :disabled="!qrData?.qrCode"
                        class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Download QR Code
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Detail Lokasi -->
        <div v-if="showDetailModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div
                class="bg-white dark:bg-neutral-800 rounded-lg shadow-2xl max-w-4xl w-full max-h-[95vh] flex flex-col overflow-hidden transform transition-all duration-300">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex-shrink-0">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-50">
                            Detail Lokasi Magang
                        </h3>
                        <button @click="closeDetailModal"
                            class="text-neutral-400 dark:text-neutral-300 hover:text-neutral-500 dark:hover:text-neutral-200 transition-colors p-1 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="overflow-y-auto p-6 space-y-8 flex-grow">
                    <!-- Dokumentasi Gambar -->
                    <div class="bg-gray-50 dark:bg-neutral-700 rounded-xl p-5 shadow-inner">
                        <h4
                            class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 pb-3 border-b border-gray-200 dark:border-neutral-600 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-1-5h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Dokumentasi Lokasi
                        </h4>

                        <div v-if="selectedLocation.images && selectedLocation.images.length"
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <div v-for="image in selectedLocation.images" :key="image.id_image"
                                class="relative group aspect-[4/3] overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                                <a :href="`/storage/${image.image_path}`" target="_blank" rel="noopener noreferrer">
                                    <img :src="`/storage/${image.image_path}`"
                                        :alt="image.alt_text || 'Dokumentasi lokasi'"
                                        class="w-full h-full object-cover border border-gray-300 dark:border-neutral-600 cursor-pointer transition-transform duration-300 group-hover:scale-110"
                                        loading="lazy" />
                                    <p
                                        class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs text-center py-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        {{ image.alt_text || 'Dokumentasi lokasi' }}
                                    </p>
                                </a>
                            </div>
                        </div>

                        <div v-else
                            class="py-8 text-center text-neutral-500 dark:text-neutral-400 border border-dashed border-gray-300 dark:border-neutral-600 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-1-5h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm">Tidak ada dokumentasi gambar yang diunggah.</p>
                        </div>
                    </div>

                    <!-- Grid untuk informasi -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <!-- Informasi Mahasiswa -->
                            <div class="bg-gray-50 dark:bg-neutral-700 rounded-xl p-5 shadow-inner">
                                <h4
                                    class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 pb-3 border-b border-gray-200 dark:border-neutral-600 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Informasi Mahasiswa
                                </h4>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 gap-3">
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Nama</label>
                                            <p class="text-sm text-neutral-900 dark:text-neutral-50 font-medium">{{
                                                selectedLocation.student_name }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">NIM</label>
                                            <p class="text-sm text-neutral-900 dark:text-neutral-50 font-mono">{{
                                                selectedLocation.nim }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Fakultas</label>
                                            <p class="text-sm text-neutral-900 dark:text-neutral-50">{{
                                                selectedLocation.department?.faculty?.name_faculty }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Jurusan</label>
                                            <p class="text-sm text-neutral-900 dark:text-neutral-50">
                                                {{ selectedLocation.department?.name_department }}
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400 ml-2">({{
                                                    selectedLocation.department?.degree_level }})</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Magang -->
                            <div class="bg-gray-50 dark:bg-neutral-700 rounded-xl p-5 shadow-inner">
                                <h4
                                    class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 pb-3 border-b border-gray-200 dark:border-neutral-600 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.004 12.004 0 003 12c0 2.75 1.255 5.24 3.243 7.027 1.8 1.62 4.1 2.973 6.094 2.973 1.994 0 4.293-1.353 6.094-2.973C19.745 17.24 21 14.75 21 12a12.004 12.004 0 00-.382-6.016z" />
                                    </svg>
                                    Status Magang
                                </h4>
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Status</label>
                                        <div>
                                            <span v-if="selectedLocation.approved_at"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Disetujui
                                            </span>
                                            <span v-else
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 10.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Menunggu Persetujuan
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="selectedLocation.approved_at"
                                        class="pt-2 border-t border-gray-200 dark:border-neutral-600">
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Disetujui
                                            pada</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50">
                                            {{ new Date(selectedLocation.approved_at).toLocaleDateString('id-ID', {
                                                weekday: 'long',
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric'
                                            }) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <!-- Informasi Lokasi -->
                            <div class="bg-gray-50 dark:bg-neutral-700 rounded-xl p-5 shadow-inner">
                                <h4
                                    class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 pb-3 border-b border-gray-200 dark:border-neutral-600 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Informasi Lokasi
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Nama
                                            Lokasi</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50 font-semibold">{{
                                            selectedLocation.name_location }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Deskripsi</label>
                                        <div class="mt-1">
                                            <p
                                                class="text-sm text-neutral-900 dark:text-neutral-50 leading-relaxed whitespace-pre-line max-h-32 overflow-y-auto pr-2">
                                                {{ selectedLocation.description || 'Tidak ada deskripsi' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Kontak</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50">{{
                                            selectedLocation.contact ||
                                            '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Koordinat Lokasi -->
                            <div class="bg-gray-50 dark:bg-neutral-700 rounded-xl p-5 shadow-inner">
                                <h4
                                    class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 pb-3 border-b border-gray-200 dark:border-neutral-600 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    Koordinat Lokasi
                                </h4>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Latitude</label>
                                            <p
                                                class="text-sm text-neutral-900 dark:text-neutral-50 font-mono bg-neutral-100 dark:bg-neutral-800 p-2 rounded">
                                                {{ selectedLocation.latitude }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Longitude</label>
                                            <p
                                                class="text-sm text-neutral-900 dark:text-neutral-50 font-mono bg-neutral-100 dark:bg-neutral-800 p-2 rounded">
                                                {{ selectedLocation.longitude }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-3 border-t border-gray-200 dark:border-neutral-600">
                                        <a :href="`https://www.google.com/maps/search/?api=1&query=${selectedLocation.latitude},${selectedLocation.longitude}`"
                                            target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-md text-sm font-medium">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Buka di Google Maps
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div v-if="selectedLocation.created_at"
                        class="pt-6 border-t border-gray-200 dark:border-neutral-700">
                        <h4
                            class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Timeline
                        </h4>
                        <div class="relative pl-8">
                            <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-neutral-700"></div>

                            <div class="relative mb-6">
                                <div
                                    class="absolute -left-8 top-0 w-6 h-6 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-primary-600 dark:text-primary-300" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 10.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h5 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1">
                                        Pengajuan Lokasi Dibuat
                                    </h5>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
                                        {{ new Date(selectedLocation.created_at).toLocaleDateString('id-ID', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </p>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-300">
                                        Mahasiswa mengajukan lokasi magang.
                                    </p>
                                </div>
                            </div>

                            <div v-if="selectedLocation.approved_at" class="relative">
                                <div
                                    class="absolute -left-8 top-0 w-6 h-6 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600 dark:text-green-300" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h5 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1">
                                        Lokasi Disetujui
                                    </h5>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">
                                        {{ new Date(selectedLocation.approved_at).toLocaleDateString('id-ID', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </p>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-300">
                                        Lokasi magang telah disetujui oleh pihak yang berwenang.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700 flex justify-end flex-shrink-0 gap-3">
                    <button @click="approveRequest(selectedLocation.id_location)" v-if="!selectedLocation.approved_at"
                        class="px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium flex items-center gap-2 transition-colors shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Setujui
                    </button>
                    <button @click="closeDetailModal"
                        class="px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium transition-colors shadow-md">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Delete -->
        <ModalDelete :show="showDeleteModal" :title="`Hapus Lokasi ${itemName}?`" :message="deleteMessage"
            :warning-message="warningMessage" :loading="deleting" @close="closeDeleteModal" @confirm="deleteItem"
            confirm-text="Hapus" />
    </AuthenticatedLayout>
</template>

<style scoped>
.th {
    @apply px-4 py-3 text-left text-sm font-semibold text-neutral-700 border-b border-gray-200;
}

.dark .th {
    @apply text-neutral-200 border-neutral-700;
}

.td {
    @apply px-4 py-3 text-sm text-neutral-700 align-top;
}

.dark .td {
    @apply text-neutral-300;
}

.btn {
    @apply px-3 py-1.5 text-xs rounded transition-colors duration-200 inline-flex items-center justify-center;
}

.btn-primary {
    @apply bg-blue-600 text-white hover:bg-blue-700;
}

.btn-secondary {
    @apply bg-gray-600 text-white hover:bg-gray-700 dark:bg-neutral-700 dark:hover:bg-neutral-600;
}

.btn-danger {
    @apply bg-red-600 text-white hover:bg-red-700;
}
</style>