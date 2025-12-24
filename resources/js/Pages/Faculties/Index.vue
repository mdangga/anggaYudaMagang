<script setup>
/* =========================
 * Core Imports
 * ========================= */
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

/* =========================
 * Layout & Components
 * ========================= */
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

/* =========================
 * TanStack Table (Vue)
 * ========================= */
import {
    createColumnHelper,
    getCoreRowModel,
    getFilteredRowModel,
    getSortedRowModel,
    getPaginationRowModel,
    useVueTable,
} from '@tanstack/vue-table'

/* =========================
 * Props
 * ========================= */
const props = defineProps({
    faculties: {
        type: Array,
        required: true,
    },
})

/* =========================
 * Table State (CLIENT SIDE)
 * ========================= */
const globalFilter = ref('')
const sorting = ref([])
const pagination = ref({
    pageIndex: 0,
    pageSize: 10,
})

/* =========================
 * Data Source
 * ========================= */
const tableData = computed(() => props.faculties)

/* =========================
 * Columns
 * ========================= */
const columnHelper = createColumnHelper()

const columns = [
    columnHelper.display({
        id: 'no',
        header: 'No',
        cell: ({ row, table }) =>
            table.getState().pagination.pageIndex *
            table.getState().pagination.pageSize +
            row.index +
            1,
    }),

    columnHelper.accessor('name_faculty', {
        header: 'Nama Fakultas',
        enableSorting: true,
    }),

    columnHelper.accessor('departments_count', {
        header: 'Total Jurusan',
        enableSorting: true,
    }),

    columnHelper.display({
        id: 'actions',
        header: 'Aksi',
    }),
]

/* =========================
 * Table Instance
 * ========================= */
const table = useVueTable({
    data: tableData,
    columns,

    state: {
        globalFilter,
        sorting,
        pagination,
    },

    onSortingChange: updater => {
        sorting.value =
            typeof updater === 'function'
                ? updater(sorting.value)
                : updater
    },

    onPaginationChange: updater => {
        pagination.value =
            typeof updater === 'function'
                ? updater(pagination.value)
                : updater
    },

    onGlobalFilterChange: value => {
        globalFilter.value = value
        pagination.value.pageIndex = 0
    },

    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
})
</script>

<template>
    <Head title="Daftar Fakultas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold">Daftar Fakultas</h2>
                    <p class="text-xs text-neutral-500">
                        Kelola fakultas (client-side)
                    </p>
                </div>

                <Link
                    :href="route('faculty.create')"
                    class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm"
                >
                    Tambah Fakultas
                </Link>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto">
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm p-4">

                <!-- TOP CONTROLS -->
                <div class="flex justify-between items-center mb-4 gap-4">
                    <input
                        v-model="globalFilter"
                        placeholder="Cari fakultas..."
                        class="border rounded px-3 py-2 text-sm w-64"
                    />

                    <select
                        v-model="pagination.pageSize"
                        class="border rounded px-2 py-1 text-sm"
                    >
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                    </select>
                </div>

                <!-- TABLE -->
                <table class="min-w-full border">
                    <thead class="bg-gray-100 dark:bg-slate-900">
                        <tr>
                            <th
                                v-for="header in table.getHeaderGroups()[0].headers"
                                :key="header.id"
                                class="th cursor-pointer select-none"
                                @click="header.column.getCanSort() && header.column.toggleSorting()"
                            >
                                {{ header.column.columnDef.header }}

                                <span v-if="header.column.getIsSorted() === 'asc'">▲</span>
                                <span v-else-if="header.column.getIsSorted() === 'desc'">▼</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="border-t"
                        >
                            <td class="td">
                                {{
                                    table.getState().pagination.pageIndex *
                                    table.getState().pagination.pageSize +
                                    row.index +
                                    1
                                }}
                            </td>

                            <td class="td">
                                {{ row.original.name_faculty }}
                            </td>

                            <td class="td">
                                {{ row.original.departments_count }}
                            </td>

                            <td class="td">
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('faculty.edit', row.original.id_faculty)"
                                        class="btn btn-info"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="table.getRowModel().rows.length === 0">
                            <td colspan="4" class="py-6 text-center text-neutral-500">
                                Data tidak ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <div class="flex justify-between items-center mt-4">
                    <span class="text-sm text-neutral-500">
                        Page {{ table.getState().pagination.pageIndex + 1 }}
                        dari {{ table.getPageCount() }}
                    </span>

                    <div class="flex gap-2">
                        <button
                            class="btn"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            Prev
                        </button>

                        <button
                            class="btn"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.th {
    @apply px-4 py-3 text-sm font-semibold border-b text-left;
}

.td {
    @apply px-4 py-3 text-sm;
}

.btn {
    @apply px-3 py-1.5 text-xs rounded bg-gray-600 text-white disabled:opacity-50;
}

.btn-info {
    @apply bg-blue-600;
}
</style>
