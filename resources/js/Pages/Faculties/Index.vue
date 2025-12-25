<script setup>
/* =========================
 * Core Imports
 * ========================= */
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import axios from 'axios'

/* =========================
 * Third-party
 * ========================= */
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

/* =========================
 * AG Grid Imports
 * ========================= */
import { AgGridVue } from 'ag-grid-vue3'
import { ModuleRegistry, AllCommunityModule, colorSchemeDarkBlue, themeQuartz } from 'ag-grid-community'

ModuleRegistry.registerModules([
    AllCommunityModule,
]);

/* =========================
 * Components
 * ========================= */
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalDelete from '@/Components/ModalDelete.vue'


/* =========================
 * Toast Helper
 * ========================= */
const notify = {
    success: (msg) => toast.success(msg, { autoClose: 5000 }),
    error: (msg) => toast.error(msg, { autoClose: 5000 })
}

/* =========================
 * AG GRID – STATE
 * ========================= */

const gridApi = ref(null)
const quickFilterText = ref('')
const rowData = ref([])
const theme = ref(null)

/* =========================
 * AG Grid Configuration
 * ========================= */

const columnDefs = ref([
    {
        headerName: 'No', 
        valueGetter: params => params.node.rowIndex + 1,
        width: 150,
        sortable: false, 
        flex: 1,
    },
    {
        field: 'name_faculty',
        headerName: 'Nama Fakultas',
        sortable: true,
        flex: 1
    },
    {
        field: 'departments_count',
        headerName: 'Total Jurusan',
        sortable: true,
        width: 150,
        cellStyle: { textAlign: 'center' }
    },
    {
        headerName: 'Aksi',
        width: 180,
        cellRenderer: (params) => {
            const wrapper = document.createElement('div')
            wrapper.className = 'flex h-full items-center gap-2'

            // Button Edit
            const editBtn = document.createElement('button')
            editBtn.className = 'px-3 py-1 text-xs bg-sky-500 text-white rounded hover:bg-sky-600 flex items-center gap-1 transition-colors'
            editBtn.innerHTML = `
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit
            `
            // Menggunakan Inertia router visit
            editBtn.onclick = () => router.visit(route('faculty.edit', params.data.id_faculty))

            // Button Delete
            const deleteBtn = document.createElement('button')
            deleteBtn.className = 'px-3 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 flex items-center gap-1 transition-colors'
            deleteBtn.innerHTML = `
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Hapus
            `
            deleteBtn.onclick = () => openDeleteModal(params.data.id_faculty, params.data.name_faculty)

            wrapper.appendChild(editBtn)
            wrapper.appendChild(deleteBtn)

            return wrapper
        }
    }
])

const gridOptions = ref({
    animateRows: false,
    pagination: true,
    paginationPageSize: 5,
    paginationPageSizeSelector: [1, 5, 10, 20, 50, 100],
    domLayout: 'autoHeight',
})

const onGridReady = (params) => {
    gridApi.value = params.api
    fetchFaculties()
}

const EXCLUDED_COLUMNS = ['aksi']

const exportCsv = () => {
    if (!gridApi.value) return

    const allColumnIds = gridApi.value
        .getColumnDefs()
        .map(col => col.field)
        .filter(Boolean)

    const exportColumnIds = allColumnIds.filter(
        colId => !EXCLUDED_COLUMNS.includes(colId)
    )

    gridApi.value.exportDataAsCsv({
        columnKeys: exportColumnIds,
        fileName: 'data-fakultas.csv'
    })
}

// Watcher untuk Filter Cepat
watch(quickFilterText, (val) => {
    if (gridApi.value) gridApi.value.setGridOption('quickFilterText', val)
})

/* =========================
 * THEME HANDLING (Dark Mode Fix)
 * ========================= */
const getTheme = (isDark) => {
    return isDark
        ? themeQuartz
            .withParams({
                spacing: 12,
                accentColor: '#3b82f6',
                backgroundColor: '#1e293b',
                foregroundColor: '#f8fafc',
                headerBackgroundColor: '#0f172a',
                borderColor: '#334155',
            })
            .withPart(colorSchemeDarkBlue)
        : themeQuartz.withParams({
            spacing: 12,
            accentColor: '#2563eb',
        })
}

// Fungsi update tema yang akan dipanggil observer
const updateGridTheme = () => {
    const isDark = document.documentElement.classList.contains('dark')
    theme.value = getTheme(isDark)
}

// Mutation Observer Variable
let themeObserver = null

/* =========================
 * AG GRID – AJAX & LIFECYCLE
 * ========================= */
const fetchFaculties = async () => {
    try {
        const { data } = await axios.get('/fakultas/ajax')
        rowData.value = data.data
    } catch (e) {
        console.error('error fetching:', e)
        notify.error('gagal memuat data')
    }
}

onMounted(() => {
    updateGridTheme()
    fetchFaculties()

    themeObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                updateGridTheme()
            }
        })
    })

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    })
})

onUnmounted(() => {
    if (themeObserver) themeObserver.disconnect()
})

/* =========================
 * DELETE LOGIC
 * ========================= */
const showDeleteModal = ref(false)
const deleting = ref(false)
const itemIdToDelete = ref(null)
const itemName = ref('')

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

const selectedFaculty = computed(() => {
    if (!itemIdToDelete.value) return null
    return props.faculties.data.find(c => c.id_faculty === itemIdToDelete.value)
})

const warningMessage = computed(() => {
    if (!selectedFaculty.value) return 'Data yang dihapus tidak dapat dikembalikan.'
    if (selectedFaculty.value.departments_count > 0) {
        return `Fakultas ini memiliki ${selectedFaculty.value.departments_count} jurusan. Menghapus fakultas akan mempengaruhi data terkait.`
    }
    return 'Data yang dihapus tidak dapat dikembalikan.'
})

const deleteMessage = computed(() => {
    return `Apakah Anda yakin ingin menghapus fakultas "${itemName.value}"?`
})

const deleteItem = () => {
    deleting.value = true
    router.delete(route('faculty.destroy', itemIdToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Fakultas berhasil dihapus !", { position: "top-right", autoClose: 3000 });
            showDeleteModal.value = false
            itemIdToDelete.value = null
            itemName.value = ''
        },
        onError: (errors) => console.error('Delete error:', errors),
        onFinish: () => deleting.value = false
    })
}
</script>

<template>
    <Head :title="`Fakultas - ${$page.props.profile_web.app_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-neutral-50">
                        Daftar Fakultas
                    </h2>
                    <p class="text-neutral-500 dark:text-neutral-300 text-xs mt-1">
                        Kelola fakultas
                    </p>
                </div>

                <Link :href="route('faculty.create')"
                    class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-l from-primary-300 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 ease-out overflow-hidden">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                    </div>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="relative text-sm">Tambah Fakultas</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="p-4">
                        <div class="flex gap-2 justify-between items-center p-3">
                            <input type="text" placeholder="Cari..."
                                class="form-input w-96 rounded-xl dark:bg-slate-700 dark:text-neutral-50"
                                v-model="quickFilterText" />

                            <!-- button download csv -->
                            <button
                                class="flex justify-between items-center gap-2 px-3 py-3 text-sm bg-emerald-600 text-white rounded-xl"
                                @click="exportCsv">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 16 16">
                                    <path fill="currentColor" fill-rule="evenodd"
                                        d="M1.5 1.5A.5.5 0 0 1 2 1h6.5a.5.5 0 0 1 .354.146l2.5 2.5A.5.5 0 0 1 11.5 4v2h-1V5H8a.5.5 0 0 1-.5-.5V2h-5v12h8v-.5h1v1a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5zm7 .707V4h1.793zm2.55 5.216A3 3 0 0 1 11 7h1q-.001.004.007.069q.01.07.029.182c.026.15.063.34.11.56c.092.44.216.982.34 1.512s.25 1.043.343 1.425l.062.252h.218l.062-.252c.093-.382.218-.896.342-1.425c.125-.53.249-1.072.341-1.512c.047-.22.085-.41.11-.56q.02-.111.029-.182L14 7h1c0 .113-.024.272-.05.423c-.03.165-.07.369-.117.594a70 70 0 0 1-.346 1.535a167 167 0 0 1-.459 1.895l-.043.174A.5.5 0 0 1 13.5 12h-1a.5.5 0 0 1-.485-.379l-.043-.174a192 192 0 0 1-.459-1.895a70 70 0 0 1-.346-1.535a18 18 0 0 1-.117-.594M4 8a1 1 0 0 1 1-1h2v1H5v3h2v1H5a1 1 0 0 1-1-1zm3.5 0a1 1 0 0 1 1-1h2v1h-2v1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1h-2v-1h2v-1h-1a1 1 0 0 1-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Download
                            </button>
                        </div>

                        <div v-if="rowData.length > 0">
                            <AgGridVue :theme="theme" :gridOptions="gridOptions" :columnDefs="columnDefs"
                                :rowData="rowData" @grid-ready="onGridReady" style="height: 100%; width: 100%;" />
                        </div>

                        <div v-else class="text-center p-8 text-gray-500 dark:text-gray-400">
                            <p>Data belum tersedia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ModalDelete :show="showDeleteModal" :title="`Hapus Fakultas ${itemName}?`" :message="deleteMessage"
            :warning-message="warningMessage" :loading="deleting" @close="closeDeleteModal" @confirm="deleteItem"
            confirm-text="Hapus" />

    </AuthenticatedLayout>
</template>