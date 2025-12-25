<script setup>
/* =========================
 * Core Imports
 * ========================= */
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, watch } from 'vue' // Tambah onUnmounted
import axios from 'axios'

/* =========================
 * Third-party
 * ========================= */
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

/* =========================
 * AG Grid
 * ========================= */
import { AgGridVue } from 'ag-grid-vue3'
import { ModuleRegistry, AllCommunityModule, colorSchemeDarkBlue, themeQuartz } from 'ag-grid-community'

ModuleRegistry.registerModules([
    AllCommunityModule,
]);

/* =========================
 * Layout & Components
 * ========================= */
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalDelete from '@/Components/ModalDelete.vue'
import LocationDetailModal from './Partials/ModalDetail.vue'
import QrCodeModal from './Partials/ModalQr.vue'

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
const rowData = ref([])
const quickFilterText = ref('')
const theme = ref(null)

const columnDefs = ref([
    {
        headerName: 'No',
        valueGetter: params => params.node.rowIndex + 1,
        sortable: false,
        width: 70,
        cellClass: 'cell-center'
    },
    {
        field: 'student_name',
        headerName: 'Nama',
        sortable: true,
        flex: 1,
        cellClass: 'cell-center'
    },
    {
        field: 'nim',
        headerName: 'NIM',
        sortable: true,
        flex: 1,
        cellClass: 'cell-center'
    },
    {
        field: 'name_location',
        headerName: 'Lokasi',
        sortable: true,
        flex: 2,
        wrapText: true,
        autoHeight: true,
        cellClass: 'cell-wrap'
    },
    {
        field: 'contact',
        headerName: 'Kontak',
        sortable: false,
        width: 150,
        cellClass: 'cell-center'
    },
    {
        field: 'department.name_department',
        headerName: 'Jurusan',
        flex: 1,
        cellClass: 'cell-center'
    },
    {
        field: 'category.name_category',
        headerName: 'Kategori',
        sortable: false,
        flex: 1,
        cellClass: 'cell-center'
    },
    {
        headerName: 'Aksi',
        cellRenderer: (params) => {
            const wrapper = document.createElement('div')
            wrapper.className = 'flex h-full justify-center items-center gap-2'

            const detailBtn = document.createElement('button')
            detailBtn.className = 'px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600'
            detailBtn.textContent = 'Detail'
            detailBtn.onclick = () => openDetail(params.data)

            const deleteBtn = document.createElement('button')
            deleteBtn.className = 'px-3 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600'
            deleteBtn.textContent = 'Hapus'
            deleteBtn.onclick = () => openDeleteModal(params.data.id_location, params.data.name_location)

            wrapper.appendChild(detailBtn)
            wrapper.appendChild(deleteBtn)
            return wrapper
        },
        sortable: false,
        width: 150
    }
])

const gridOptions = ref({
    animateRows: false,
    pagination: true,
    domLayout: 'autoHeight',
    paginationPageSize: 5,
    paginationPageSizeSelector: [1, 5, 10, 20, 50, 100],
})

const onGridReady = (params) => {
    gridApi.value = params.api
    fetchLocations()
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
        processCellCallback: ({ column, value }) => {
            const colId = column.getColId()

            if (['nim', 'contact'].includes(colId)) {
                return `'${value}`
            }

            return value
        },
        fileName: 'data-lokasi-magang.csv'
    })
}


watch(quickFilterText, (val) => {
    if (!gridApi.value) return
    gridApi.value.setGridOption('quickFilterText', val)
})

/* =========================
 * THEME HANDLING (FIXED)
 * ========================= */
// Fungsi untuk generate object tema berdasarkan kondisi
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
const fetchLocations = async () => {
    try {
        const response = await fetch('/locations/ajax', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()
        rowData.value = data.data
    } catch (e) {
        console.error('Error fetching locations:', e)
        notify.error('Gagal memuat data lokasi')
    }
}


onMounted(() => {
    updateGridTheme()
    fetchLocations()

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
    if (themeObserver) {
        themeObserver.disconnect()
    }
})

/* =========================
 * QR CODE, DETAIL, DELETE, APPROVE (TETAP SAMA)
 * ========================= */
const showQrModal = ref(false)
const qrLoading = ref(false)

const generateQrCode = () => { showQrModal.value = true }

const handleQrGenerated = (event) => {
    if (event.type === 'copy') notify.success(event.message)
}

const handleQrError = (event) => {
    if (event.type === 'generate') notify.error('Gagal generate QR Code')
}

const showDetailModal = ref(false)
const selectedLocation = ref(null)

const openDetail = (row) => {
    selectedLocation.value = row
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
    selectedLocation.value = null
}

const showDeleteModal = ref(false)
const deleting = ref(false)
const itemName = ref('')
const deleteMessage = ref('')
const warningMessage = 'Tindakan ini tidak dapat dibatalkan'
const deleteTargetId = ref(null)

const openDeleteModal = (id, name) => {
    deleteTargetId.value = id
    itemName.value = name
    deleteMessage.value = `Apakah Anda yakin ingin menghapus lokasi "${name}"?`
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    if (deleting.value) return
    showDeleteModal.value = false
    deleteTargetId.value = null
    itemName.value = ''
    deleteMessage.value = ''
}

const deleteItem = async () => {
    deleting.value = true
    try {
        await router.delete(route('locations.destroy', deleteTargetId.value), {
            preserveScroll: true
        })
        notify.success('Lokasi berhasil dihapus')
        fetchLocations()
        closeDeleteModal()
    } catch {
        notify.error('Gagal menghapus lokasi')
    } finally {
        deleting.value = false
    }
}

const approveRequest = async (id) => {
    try {
        await router.post(route('locations.approve', id), {}, { preserveScroll: true })
        notify.success('Lokasi disetujui')
        fetchLocations()
        updateGridTheme()
        closeDetailModal()
    } catch {
        notify.error('Gagal menyetujui lokasi')
    }
}
</script>

<template>
    <Head :title="`Lokasi - ${$page.props.profile_web.app_name}`" />

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

                <button @click="generateQrCode" :disabled="qrLoading"
                    class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-l from-primary-300 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 ease-out overflow-hidden"
                    :class="{ 'opacity-70 cursor-not-allowed': qrLoading }">

                    <!-- Shimmer Effect -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                    </div>

                    <!-- Loading Spinner -->
                    <svg v-if="qrLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
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
                        {{ qrLoading ? 'Memproses...' : 'Generate QR' }}
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
                        <div v-else class="text-center p-8 text-gray-500">
                            Tidak ada data.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <QrCodeModal :show="showQrModal" @close="showQrModal = false" @generated="handleQrGenerated"
            @error="handleQrError" />

        <LocationDetailModal :show="showDetailModal" :location="selectedLocation" @close="closeDetailModal">
            <template #actions="{ location, close }">
                <button @click="approveRequest(location.id_location)" v-if="location && !location.approved_at"
                    class="bg-green-600 text-white px-4 py-2 rounded">Setujui</button>
                <button @click="close" class="bg-gray-500 text-white px-4 py-2 rounded">Tutup</button>
            </template>
        </LocationDetailModal>

        <ModalDelete :show="showDeleteModal" :title="`Hapus ${itemName}?`" :message="deleteMessage"
            :warning-message="warningMessage" :loading="deleting" @close="closeDeleteModal" @confirm="deleteItem" />

    </AuthenticatedLayout>
</template>

<style scoped>
.cell-center {
    display: flex;
    align-items: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
}

.cell-wrap {
    display: flex;
    align-items: center;
    /* tetap vertical center */
    white-space: normal;
    /* AKTIF word wrap */
    line-height: 1.4;
}
</style>