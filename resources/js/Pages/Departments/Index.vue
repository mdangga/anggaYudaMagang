<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalDelete from '@/Components/ModalDelete.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, inject, computed } from 'vue'

const props = defineProps({
    departments: Object
})

const showDeleteModal = ref(false)
const deleting = ref(false)
const itemIdToDelete = ref(null)
const itemName = ref('')

// Inject dark mode dari layout
const darkMode = inject('darkMode', ref(false))
const toggleDarkMode = inject('toggleDarkMode', () => { })

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

const selectedDepartment = computed(() => {
    if (!itemIdToDelete.value) return null
    return props.departments.data.find(
        c => c.id_department === itemIdToDelete.value
    )
})

const warningMessage = computed(() => {
    if (!selectedDepartment.value) {
        return 'Data yang dihapus tidak dapat dikembalikan.'
    }

    if (selectedDepartment.value.locations_count > 0) {
        return `Jurusan ini memiliki ${selectedDepartment.value.locations_count} lokasi. Menghapus jurusan akan mempengaruhi data terkait.`
    }

    return 'Data yang dihapus tidak dapat dikembalikan.'
})

const deleteMessage = computed(() => {
    return `Apakah Anda yakin ingin menghapus jurusan "${itemName.value}"? Tindakan ini tidak dapat dibatalkan.`
})

const deleteItem = () => {
    deleting.value = true

    router.delete(route('department.destroy', itemIdToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            itemIdToDelete.value = null
            itemName.value = ''
        },
        onError: (errors) => {
            console.error('Delete error:', errors)
        },
        onFinish: () => {
            deleting.value = false
        }
    })
}
</script>

<template>

    <Head title="Daftar Jurusan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">
                        Daftar Jurusan
                    </h2>
                    <p class="text-neutral-500 dark:text-neutral-300 text-xs mt-1">
                        Kelola jurusan
                    </p>
                </div>

                <Link :href="route('department.create')"
                    class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-l from-primary-300 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 ease-out overflow-hidden">

                    <!-- Shimmer Effect -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                    </div>

                    <!-- Add Icon -->
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-12"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>

                    <!-- Button Text -->
                    <span class="relative text-sm">
                        Tambah Jurusan
                    </span>

                    <!-- Tooltip -->
                    <div
                        class="absolute bottom-full mb-2 hidden group-hover:block px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap">
                        Tambah Jurusan Baru
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
                                        <th class="th">Nama Jurusan</th>
                                        <th class="th">Nama Fakultas</th>
                                        <th class="th">Jumlah Lokasi</th>
                                        <th class="th text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="(item, index) in departments.data" :key="item.id_department"
                                        class="border-t dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-700">
                                        <td class="td">
                                            {{ index + 1 + (departments.current_page - 1) * departments.per_page }}
                                        </td>
                                        <td class="td">
                                            {{ item.name_department }}
                                        </td>
                                        <td class="td">
                                            {{ item.faculty.name_faculty }}
                                        </td>
                                        <td class="td">
                                            {{ item.locations_count }}
                                        </td>
                                        <td class="td text-start space-x-2">
                                            <Link :href="route('department.edit', item.id_department)"
                                                class="group relative inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-gradient-to-l from-info-300 to-info-600 hover:from-info-600 hover:to-info-700 text-neutral-900 dark:text-gray-200 hover:text-white font-medium rounded-md shadow hover:shadow-md transition-all duration-300 ease-out overflow-hidden text-sm">

                                                <!-- Shimmer Effect -->
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                                                </div>

                                                <!-- Edit Icon -->
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-12"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>

                                                <!-- Button Text -->
                                                <span class="relative">
                                                    Edit
                                                </span>
                                            </Link>

                                            <button @click="openDeleteModal(item.id_department, item.name_department)"
                                                class="group relative inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-gradient-to-l from-danger-300 to-danger-600 hover:from-danger-600 hover:to-danger-700 text-neutral-900 dark:text-gray-200 hover:text-white font-medium rounded-md shadow hover:shadow-md transition-all duration-300 ease-out overflow-hidden text-sm">

                                                <!-- Shimmer Effect -->
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                                                </div>

                                                <!-- Trash Icon -->
                                                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-12"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                                    <tr v-if="departments.data.length === 0">
                                        <td colspan="9" class="text-center py-6 text-neutral-500 dark:text-neutral-400">
                                            Data belum tersedia
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <div class="flex gap-1">
                                <template v-for="link in departments.links" :key="link.label">
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

        <ModalDelete :show="showDeleteModal" :title="`Hapus Jurusan ${itemName}?`" :message="deleteMessage"
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