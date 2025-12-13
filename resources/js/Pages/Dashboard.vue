<script setup>
import { ref, onMounted, computed, watch, onUnmounted, inject } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'
import MapComponent from '@/Components/Map.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// Data state
const locations = ref([])
const categories = ref([])
const pendingRequests = ref([])
const searchQuery = ref('')
const selectedLocation = ref(null)
const filteredResults = ref([])
const showAutocomplete = ref(false)
const highlightedIndex = ref(-1)
const sidebarOpen = ref(false)
const isMobile = ref(false)

// Inject dark mode dari layout
const darkMode = inject('darkMode', ref(false))
const toggleDarkMode = inject('toggleDarkMode', () => { })

// Map reference
const mapComponentRef = ref(null)
const mapKey = ref(Date.now())

// Computed
const filteredLocations = computed(() => {
    if (!searchQuery.value.trim()) return locations.value
    const query = searchQuery.value.toLowerCase()
    return locations.value.filter(location =>
        location.name_location.toLowerCase().includes(query) ||
        location.category_name.toLowerCase().includes(query) ||
        location.description.toLowerCase().includes(query)
    )
})

// Methods
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768
}

const selectLocation = (location) => {
    selectedLocation.value = location
    showAutocomplete.value = false
    highlightedIndex.value = -1

    // Open sidebar on mobile
    if (isMobile.value) sidebarOpen.value = true

    const footer = document.getElementById('sidebar-footer')
    if (footer) footer.classList.add('hidden')
}

const clearSelection = () => {
    selectedLocation.value = null
    sidebarOpen.value = false

    // Reset map view
    if (mapComponentRef.value) {
        mapComponentRef.value.clearSelectionOnMap()
    }

    document.getElementById('sidebar-footer')?.classList.remove('hidden')
}

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        filteredResults.value = filteredLocations.value.slice(0, 5)
        showAutocomplete.value = true
        highlightedIndex.value = -1
    } else {
        filteredResults.value = []
        showAutocomplete.value = false
    }
}

const clearSearch = () => {
    searchQuery.value = ''
    filteredResults.value = []
    showAutocomplete.value = false
    highlightedIndex.value = -1
}

const highlightNext = (e) => {
    e.preventDefault()
    if (filteredResults.value.length > 0) {
        highlightedIndex.value = (highlightedIndex.value + 1) % filteredResults.value.length
    }
}

const highlightPrev = (e) => {
    e.preventDefault()
    if (filteredResults.value.length > 0) {
        highlightedIndex.value = highlightedIndex.value <= 0
            ? filteredResults.value.length - 1
            : highlightedIndex.value - 1
    }
}

const selectHighlighted = (e) => {
    e.preventDefault()
    if (highlightedIndex.value >= 0 && filteredResults.value[highlightedIndex.value]) {
        selectLocation(filteredResults.value[highlightedIndex.value])
    }
}

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value

    // Invalidate map size when sidebar toggles
    setTimeout(() => {
        if (mapComponentRef.value) {
            mapComponentRef.value.invalidateSize()
        }
    }, 300)
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const navigateToLocation = (location) => {
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${location.latitude},${location.longitude}`, '_blank')
}

const openInMaps = (lat, lng) => {
    window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, '_blank')
}

const handleResize = () => {
    checkMobile()
    if (mapComponentRef.value) {
        clearTimeout(window.mapResizeTimeout)
        window.mapResizeTimeout = setTimeout(() => {
            mapComponentRef.value.invalidateSize()
        }, 250)
    }
}

// Map event handler
const onMapInitialized = (mapInstance) => {
    return mapInstance
}

const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

const series = computed(() => [
    {
        name: 'Jumlah Lokasi',
        data: categories.value.map(item => item.locations_count)
    }
]);

const chartOptions = computed(() => {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e2e8f0' : '#374151';
    const gridColor = isDark ? '#334155' : '#e5e7eb';

    return {
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false },
            background: 'transparent',
            foreColor: textColor
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '45%',
                distributed: true
            }
        },
        dataLabels: {
            enabled: false
        },
        series: [{
            name: 'Jumlah Lokasi',
            data: categories.value.map(item => item.locations_count || 0)
        }],
        xaxis: {
            categories: categories.value.map(item => item.name_category || 'Unknown'),
            labels: {
                style: {
                    colors: textColor,
                    fontSize: '12px'
                },
                rotate: -45
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: textColor
                },
                formatter: function (val) {
                    return Math.floor(val); // Hilangkan desimal
                }
            },
            title: {
                text: 'Jumlah Lokasi',
                style: {
                    color: textColor
                }
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4,
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: {
                formatter: function (val) {
                    return val + ' lokasi';
                }
            }
        },
        colors: [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#8b5cf6'
        ],
        responsive: [{
            breakpoint: 640,
            options: {
                plotOptions: {
                    bar: {
                        columnWidth: '60%'
                    }
                },
                xaxis: {
                    labels: {
                        rotate: -90
                    }
                }
            }
        }]
    };
});

// Lifecycle
onMounted(async () => {
    checkMobile()

    // Fetch locations
    await getLocation()
    await getLocationRequest()
    await getCategories()

    // Setup event listeners
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-box')) {
            showAutocomplete.value = false
        }
    })

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (isMobile.value && sidebarOpen.value &&
            !e.target.closest('#sidebar') &&
            !e.target.closest('.search-container') &&
            !e.target.closest('.mobile-sidebar-toggle')) {
            toggleSidebar()
        }
    })

    // Handle window resize
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
})

async function getLocation() {
    try {
        const res = await fetch('/get-location')
        if (!res.ok) throw new Error('Fetch failed')
        const data = await res.json()
        locations.value = Array.isArray(data) ? data : []
        return locations.value
    } catch (err) {
        console.error('Error loading locations:', err)
        locations.value = []
        return []
    }
}

async function getLocationRequest() {
    try {
        const res = await fetch('/request-locations/get-location?status=pending')
        if (!res.ok) throw new Error('Fetch failed')
        const data = await res.json()
        pendingRequests.value = Array.isArray(data) ? data : []
        return pendingRequests.value
    } catch (err) {
        console.error('Error loading locations request:', err)
        pendingRequests.value = []
        return []
    }
}

async function getCategories() {
    try {
        const res = await fetch('categories/get-categories')
        if (!res.ok) throw new Error('Fetch failed')
        const data = await res.json()
        categories.value = Array.isArray(data) ? data : []
        return categories.value
    } catch (err) {
        console.error('Error loading categories:', err)
        categories.value = []
        return []
    }
}

async function approveRequest(id) {
    try {
        await router.post(route('request-locations.approve', id));
        const idx = pendingRequests.value.findIndex(r => r.id_request === id || r.id === id)
        if (idx !== -1) pendingRequests.value.splice(idx, 1)
        await getLocation()
        await getLocationRequest()
        mapKey.value = Date.now()
    } catch (err) {
        console.error('Approve failed:', err)
    }
}

async function rejectRequest(id) {
    try {
        await router.post(route('request-locations.destroy', id));
        const idx = pendingRequests.value.findIndex(r => r.id_request === id || r.id === id)
        if (idx !== -1) pendingRequests.value.splice(idx, 1)
        await getLocation()
        await getLocationRequest()
        mapKey.value = Date.now()
    } catch (err) {
        console.error('Reject failed:', err)
    }
}

// Watchers
watch(sidebarOpen, (newValue) => {
    if (mapComponentRef.value) {
        setTimeout(() => {
            mapComponentRef.value.invalidateSize()
        }, 300)
    }
})

watch(selectedLocation, (newLocation) => {
    if (!newLocation && sidebarOpen.value && isMobile.value) {
        sidebarOpen.value = false
    }
})
</script>

<template>

    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-50">
                    Dashboard Admin
                </h2>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    {{ currentDate }}
                </div>
            </div>
        </template>

        <div class="p-4 md:p-6">
            <!-- Map Section -->
            <div
                class="lg:col-span-2 bg-white border border-gray-200 dark:bg-slate-800 dark:border-slate-700 rounded-lg overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-50 flex items-center">
                        <i class="fas fa-map mr-2"></i>
                        Peta Lokasi Magang
                    </h3>
                </div>
                <div class="p-4">
                    <div class="h-96 w-full rounded-md overflow-hidden">
                        <MapComponent :key="mapKey" ref="mapComponentRef" :locations="locations"
                            :selected-location="selectedLocation" :sidebar-open="sidebarOpen"
                            @location-selected="selectLocation" @map-initialized="onMapInitialized" />
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Statistics Chart -->
                <div
                    class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-50 mb-4 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-blue-600 dark:text-blue-400"></i>
                        Statistik Lokasi per Kategori
                    </h3>

                    <VueApexCharts type="bar" height="300" :options="chartOptions" :series="series"></VueApexCharts>
                </div>


                <!-- Pending Requests -->
                <div
                    class="bg-white border border-gray-200 dark:bg-slate-800 dark:border-slate-700 rounded-lg overflow-hidden">
                    <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-slate-700">
                        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-50 flex items-center">
                            <i class="fas fa-inbox mr-2"></i>
                            Permintaan Tertunda
                        </h3>
                        <span
                            class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-semibold dark:bg-amber-800 dark:text-amber-100">
                            {{ pendingRequests.length }}
                        </span>
                    </div>
                    <div class="p-4 max-h-72 overflow-y-auto">
                        <div v-if="pendingRequests.length === 0" class="p-8 text-center text-gray-400">
                            <i class="fas fa-check-circle text-2xl"></i>
                            <p class="mt-2">Tidak ada permintaan pending</p>
                        </div>

                        <div v-for="request in pendingRequests" :key="request.id"
                            class="p-3 border border-gray-200 dark:border-slate-700 rounded mb-2 flex justify-between items-center transition hover:bg-gray-50 dark:hover:bg-slate-700">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-700 truncate dark:text-gray-50">
                                    {{ request.name_location }}
                                </div>
                                <div class="flex flex-wrap gap-3 text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="flex items-center">
                                        <i class="fas fa-user mr-2"></i>{{ request.student_name }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-calendar mr-2"></i>{{ request.created_at }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button @click="approveRequest(request.id_request)"
                                    class="w-8 h-8 rounded-md bg-green-500 hover:bg-green-600 text-white flex items-center justify-center transition-colors"
                                    title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button @click="rejectRequest(request.id_request)"
                                    class="w-8 h-8 rounded-md bg-red-500 hover:bg-red-600 text-white flex items-center justify-center transition-colors"
                                    title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Styles yang dibutuhkan untuk map dark mode */
:deep(.leaflet-pane),
:deep(.leaflet-tile),
:deep(.leaflet-marker-icon),
:deep(.leaflet-marker-shadow),
:deep(.leaflet-tile-container),
:deep(.leaflet-pane > svg),
:deep(.leaflet-pane > canvas) {
    transition: filter 0.3s ease;
}

:deep(.leaflet-container) {
    font-family: inherit;
}

/* Style untuk custom marker */
:deep(.custom-marker) {
    background: none;
    border: none;
}

/* Dark mode styles for map */
:deep(.dark .leaflet-tile-pane) {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%) !important;
}

:deep(.dark .leaflet-layer) {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%) !important;
}
</style>