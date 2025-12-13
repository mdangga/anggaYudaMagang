<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, inject } from 'vue'

defineProps({
    categories: Object
})

const showDetailModal = ref(false)
const selectedLocation = ref(null)

// Inject dark mode dari layout
const darkMode = inject('darkMode', ref(false))
const toggleDarkMode = inject('toggleDarkMode', () => { })

const openDetailModal = (location) => {
    selectedLocation.value = location
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
    selectedLocation.value = null
}
</script>

<template>

    <Head title="Daftar Lokasi Magang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">
                        Daftar kategori lokasi magang
                    </h2>
                    <p class="text-neutral-500 dark:text-neutral-300 text-sm mt-1">
                        Kelola kategori lokasi magang mahasiswa
                    </p>
                </div>

                <Link :href="route('category.create')"
                    class="group relative inline-flex items-center justify-center gap-3 px-6 py-3.5 bg-gradient-to-l from-primary-300 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 ease-out overflow-hidden"
                    :class="{ 'opacity-70 cursor-not-allowed pointer-events-none': loading }" :disabled="loading"
                    preserve-scroll>

                <!-- Shimmer Effect -->
                <div
                    class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                </div>

                <!-- Add Icon -->
                <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-12"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                <!-- Button Text -->
                <span class="relative">
                    Tambah Kategori
                </span>

                <!-- Tooltip -->
                <div
                    class="absolute bottom-full mb-2 hidden group-hover:block px-3 py-2 bg-gray-900 text-white text-xs rounded-lg whitespace-nowrap">
                    Tambah Kategori baru
                    <div
                        class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900">
                    </div>
                </div>
                </Link>
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
                                        <th class="th">Nama Kategori</th>
                                        <th class="th">Total Lokasi</th>
                                        <th class="th text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="(item, index) in categories.data" :key="item.id_category"
                                        class="border-t dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-700">
                                        <td class="td">
                                            {{ index + 1 + (categories.current_page - 1) * categories.per_page }}
                                        </td>
                                        <td class="td">
                                            {{ item.name_category }}
                                        </td>
                                        <td class="td">
                                            {{ item.locations_count }}
                                        </td>
                                        <td class="td text-start space-x-2">
                                            <Link :href="route('category.edit', item.id_category)"
                                                class="btn btn-secondary">
                                                Edit
                                            </Link>
                                            <button class="btn btn-danger">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>

                                    <tr v-if="categories.data.length === 0">
                                        <td colspan="9" class="text-center py-6 text-neutral-500 dark:text-neutral-400">
                                            Data belum tersedia
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <div class="flex gap-1">
                                <template v-for="link in categories.links" :key="link.label">
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