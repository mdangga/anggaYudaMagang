<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    location: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['close', 'approve'])

const close = () => {
    emit('close')
}

const approve = (id) => {
    emit('approve', id)
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div
            class="bg-white dark:bg-neutral-800 rounded-lg shadow-2xl max-w-4xl w-full max-h-[95vh] flex flex-col overflow-hidden transform transition-all duration-300">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex-shrink-0">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-50">
                        Detail Lokasi Magang
                    </h3>
                    <button @click="close"
                        class="text-neutral-400 dark:text-neutral-300 hover:text-neutral-500 dark:hover:text-neutral-200 transition-colors p-1 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="overflow-y-auto p-6 space-y-8 flex-grow" v-if="location">
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

                    <div v-if="location.images && location.images.length"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <div v-for="image in location.images" :key="image.id_image"
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
                                            location.student_name }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">NIM</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50 font-mono">{{
                                            location.nim }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Fakultas</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50">{{
                                            location.department?.faculty?.name_faculty || '-' }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Jurusan</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50">
                                            {{ location.department?.name_department || '-' }}
                                            <span v-if="location.department?.degree_level"
                                                class="text-xs text-neutral-500 dark:text-neutral-400 ml-2">
                                                ({{ location.department.degree_level }})
                                            </span>
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
                                        <span v-if="location.approved_at"
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
                                <div v-if="location.approved_at"
                                    class="pt-2 border-t border-gray-200 dark:border-neutral-600">
                                    <label
                                        class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Disetujui
                                        pada</label>
                                    <p class="text-sm text-neutral-900 dark:text-neutral-50">
                                        {{ new Date(location.approved_at).toLocaleDateString('id-ID', {
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
                                        location.name_location }}</p>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Deskripsi</label>
                                    <div class="mt-1">
                                        <p
                                            class="text-sm text-neutral-900 dark:text-neutral-50 leading-relaxed whitespace-pre-line max-h-32 overflow-y-auto pr-2">
                                            {{ location.description || 'Tidak ada deskripsi' }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Kontak</label>
                                    <p class="text-sm text-neutral-900 dark:text-neutral-50">{{
                                        location.contact ||
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
                                            {{ location.latitude || '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Longitude</label>
                                        <p
                                            class="text-sm text-neutral-900 dark:text-neutral-50 font-mono bg-neutral-100 dark:bg-neutral-800 p-2 rounded">
                                            {{ location.longitude || '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="pt-3 border-t border-gray-200 dark:border-neutral-600"
                                    v-if="location.latitude && location.longitude">
                                    <a :href="`https://www.google.com/maps/search/?api=1&query=${location.latitude},${location.longitude}`"
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
                <div v-if="location.created_at" class="pt-6 border-t border-gray-200 dark:border-neutral-700">
                    <h4 class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 flex items-center gap-2">
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
                                    {{ new Date(location.created_at).toLocaleDateString('id-ID', {
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

                        <div v-if="location.approved_at" class="relative">
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
                                    {{ new Date(location.approved_at).toLocaleDateString('id-ID', {
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
            <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700 flex justify-end flex-shrink-0 gap-3">
                <slot name="actions" :location="location" :close="close">
                    <button @click="close"
                        class="px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium transition-colors shadow-md">
                        Tutup
                    </button>
                </slot>
            </div>
        </div>
    </div>
</template>