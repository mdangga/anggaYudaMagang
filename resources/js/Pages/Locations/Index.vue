<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, inject } from 'vue'
import axios from 'axios'

defineProps({
    locations: Object
})

// State untuk QR Code
const showQrModal = ref(false)
const qrData = ref(null)
const loading = ref(false)
const error = ref(null)
const showDetailModal = ref(false)
const selectedLocation = ref(null)

// Inject dark mode dari layout
const darkMode = inject('darkMode', ref(false))
const toggleDarkMode = inject('toggleDarkMode', () => { })

// Function untuk generate QR Code
const generateQrCode = async () => {
    try {
        loading.value = true
        error.value = null

        const response = await axios.post(`/locations/generate-link`)

        if (response.data.link) {
            qrData.value = {
                link: response.data.link,
                qrCode: await generateQRCodeImage(response.data.link),
                timestamp: new Date().toLocaleString()
            }
            showQrModal.value = true
        }
    } catch (err) {
        console.error('Error generating QR:', err)
        error.value = 'Gagal generate QR Code. Silakan coba lagi.'

        // Tampilkan alert error
        alert(error.value)
    } finally {
        loading.value = false
    }
}

const generateQRCodeImage = async (link) => {
    // import QRCode from 'qrcode'
    // const qrCode = await QRCode.toDataURL(link)
    // return qrCode
    try {
        const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(link)}`
        return qrCodeUrl
    } catch (error) {
        console.error('Error generating QR image:', error)
        return null
    }
}

const downloadQRCode = () => {
    if (!qrData.value) return

    const downloadLink = document.createElement('a')
    downloadLink.href = qrData.value.qrCode
    downloadLink.download = `qr-code-lokasi-${qrData.value.locationId}.png`
    document.body.appendChild(downloadLink)
    downloadLink.click()
    document.body.removeChild(downloadLink)
}

const copyLink = () => {
    if (!qrData.value) return

    navigator.clipboard.writeText(qrData.value.link)
        .then(() => {
            alert('Link berhasil disalin ke clipboard!')
        })
        .catch(err => {
            console.error('Gagal menyalin link:', err)
            alert('Gagal menyalin link')
        })
}

const openDetailModal = (location) => {
    selectedLocation.value = location
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
    selectedLocation.value = null
}

async function approveRequest(id) {
    try {
        await router.post(route('locations.approve', id));
        closeDetailModal();
    } catch (err) {
        console.error('Approve failed:', err)
    }
}
</script>

<template>

    <Head title="Daftar Lokasi Magang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">
                        Daftar Lokasi Magang
                    </h2>
                    <p class="text-neutral-500 dark:text-neutral-300 text-sm mt-1">
                        Kelola lokasi magang
                    </p>
                </div>

                <button @click="generateQrCode()" :disabled="loading"
                    class="group relative inline-flex items-center justify-center gap-3 px-6 py-3.5 bg-gradient-to-l from-primary-300 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 ease-out overflow-hidden"
                    :class="{ 'opacity-70 cursor-not-allowed': loading }">

                    <div
                        class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                    </div>

                    <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>

                    <svg v-else
                        class="w-5 h-5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-12"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>

                    <span class="relative">
                        {{ loading ? 'Memproses...' : 'Generate QR' }}
                    </span>

                    <div
                        class="absolute bottom-full mb-2 hidden group-hover:block px-3 py-2 bg-gray-900 text-white text-xs rounded-lg whitespace-nowrap">
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
                <div class="bg-white dark:bg-neutral-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="p-6">

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 dark:border-neutral-700">
                                <thead class="bg-gray-100 dark:bg-neutral-800">
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
                                        class="border-t dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-700">
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
                                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
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
                                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
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
                                            <button @click="openDetailModal(item)" class="btn btn-secondary">
                                                Detail
                                            </button>
                                            <button class="btn btn-danger">
                                                Hapus
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

                        <div class="mt-6 flex justify-end">
                            <div class="flex gap-1">
                                <template v-for="link in locations.links" :key="link.label">
                                    <a v-if="link.url" :href="link.url" v-html="link.label"
                                        class="px-3 py-1 border rounded text-sm" :class="{
                                            'bg-blue-600 text-white': link.active
                                        }" />
                                </template>
                            </div>
                        </div>

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
                            QR Code Lokasi Magang
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
                            <div class="border-2 border-gray-200 dark:border-neutral-700 p-4 rounded-lg">
                                <img :src="qrData.qrCode" alt="QR Code" class="w-64 h-64" @error="qrData.qrCode = null">

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

                <div class="overflow-y-auto p-6 space-y-8 flex-grow">

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
                                class="relative group aspect-w-4 aspect-h-3 overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                                <img :src="`/storage/${image.image_path}`" :alt="image.alt_text || 'Dokumentasi lokasi'"
                                    class="w-full h-full object-cover border border-gray-300 dark:border-neutral-600 cursor-pointer
                                   transition-transform duration-300 group-hover:scale-110" loading="lazy" />
                            </div>
                        </div>

                        <div v-else
                            class="py-4 text-center text-neutral-500 dark:text-neutral-400 border border-dashed border-gray-300 dark:border-neutral-600 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-1-5h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm">Tidak ada dokumentasi gambar yang diunggah.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
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
                                <div class="space-y-3 text-sm">
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Nama
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50">
                                            {{ selectedLocation.student_name }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            NIM
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50">
                                            {{ selectedLocation.nim }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Fakultas
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50">
                                            {{ selectedLocation.department?.faculty?.name_faculty }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Jurusan
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50">
                                            {{ selectedLocation.department?.name_department }}
                                            ({{ selectedLocation.department?.degree_level }})
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                <div class="space-y-3 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-neutral-700 dark:text-neutral-300">
                                            Status
                                        </span>
                                        <span v-if="selectedLocation.approved_at"
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Disetujui
                                        </span>
                                        <span v-else
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Menunggu Persetujuan
                                        </span>
                                    </div>
                                    <div v-if="selectedLocation.approved_at" class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Disetujui pada
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50">
                                            {{ new Date(selectedLocation.approved_at).toLocaleDateString('id-ID', {
                                                weekday: 'long',
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric'
                                            }) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
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
                                <div class="space-y-3 text-sm">
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Nama Lokasi
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50 font-semibold">
                                            {{ selectedLocation.name_location }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Deskripsi
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50 italic">
                                            {{ selectedLocation.description || 'Tidak ada deskripsi' }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Kontak
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50">
                                            {{ selectedLocation.contact || '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                <div class="space-y-3 text-sm">
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Latitude
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50 font-mono">
                                            {{ selectedLocation.latitude }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 font-medium text-neutral-700 dark:text-neutral-300">
                                            Longitude
                                        </div>
                                        <div class="w-2/3 text-neutral-900 dark:text-neutral-50 font-mono">
                                            {{ selectedLocation.longitude }}
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-neutral-600">
                                        <a :href="`https://www.google.com/maps/search/?api=1&query=${selectedLocation.latitude},${selectedLocation.longitude}`"
                                            target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md text-sm">
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

                    <div v-if="selectedLocation.created_at"
                        class="mt-8 pt-6 border-t border-gray-200 dark:border-neutral-700">
                        <h4
                            class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Timeline
                        </h4>
                        <ol class="relative border-l border-gray-200 dark:border-neutral-700 ml-4">
                            <li class="mb-6 ml-6">
                                <span
                                    class="absolute flex items-center justify-center w-6 h-6 bg-primary-100 rounded-full -left-3 ring-8 ring-white dark:ring-neutral-800 dark:bg-primary-900">
                                    <svg class="w-3 h-3 text-primary-600 dark:text-primary-300" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 10.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <h5
                                    class="flex items-center mb-1 text-sm font-semibold text-neutral-900 dark:text-white">
                                    Pengajuan Lokasi Dibuat
                                </h5>
                                <time
                                    class="block mb-2 text-xs font-normal leading-none text-neutral-400 dark:text-neutral-500">
                                    {{ new Date(selectedLocation.created_at).toLocaleDateString('id-ID', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}
                                </time>
                                <p class="text-sm font-normal text-neutral-500 dark:text-neutral-400">
                                    Mahasiswa mengajukan lokasi magang.
                                </p>
                            </li>

                            <li v-if="selectedLocation.approved_at" class="ml-6">
                                <span
                                    class="absolute flex items-center justify-center w-6 h-6 bg-green-100 rounded-full -left-3 ring-8 ring-white dark:ring-neutral-800 dark:bg-green-900">
                                    <svg class="w-3 h-3 text-green-600 dark:text-green-300" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <h5
                                    class="flex items-center mb-1 text-sm font-semibold text-neutral-900 dark:text-white">
                                    Lokasi Disetujui
                                </h5>
                                <time
                                    class="block mb-2 text-xs font-normal leading-none text-neutral-400 dark:text-neutral-500">
                                    {{ new Date(selectedLocation.approved_at).toLocaleDateString('id-ID', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}
                                </time>
                                <p class="text-sm font-normal text-neutral-500 dark:text-neutral-400">
                                    Lokasi magang telah disetujui oleh pihak yang berwenang.
                                </p>
                            </li>
                        </ol>
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700 flex justify-end flex-shrink-0 gap-3">
                    <button @click="approveRequest(selectedLocation.id_location)" v-if="!selectedLocation.approved_at"
                        class="px-5 py-2 rounded-md bg-green-600 hover:bg-green-500 text-white flex items-center justify-center transition-colors"
                        title="Setujui">
                        approve
                    </button>
                    <button @click="closeDetailModal"
                        class="px-5 py-2 rounded-md bg-primary-600 hover:bg-primary-300 text-white flex items-center justify-center transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
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