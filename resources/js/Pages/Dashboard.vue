<script setup>
import { ref, onMounted, computed, watch, onUnmounted, inject } from 'vue'
import { Head } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'
import MapComponent from '@/Components/Map.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// Data state
const locations = ref([])
const categories = ref([])
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
        const res = await fetch('/locations/get-locations')
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M19 4h-2V3a1 1 0 0 0-2 0v1H9V3a1 1 0 0 0-2 0v1H5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3m1 15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-7h16Zm0-9H4V7a1 1 0 0 1 1-1h2v1a1 1 0 0 0 2 0V6h6v1a1 1 0 0 0 2 0V6h2a1 1 0 0 1 1 1Z" />
                    </svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-primary-600"
                            viewBox="0 0 48 48">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                d="M35.555 6.566H38.5c2.216 0 4 1.784 4 4v29c0 2.216-1.784 4-4 4h-29c-2.216 0-4-1.784-4-4v-29c0-2.216 1.784-4 4-4h14.02"
                                stroke-width="4" />
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                d="M29.538 4.434a9.554 9.554 0 0 0-9.554 9.554c0 7.477 7.314 16.49 9.207 18.699a.577.577 0 0 0 .882-.004c1.861-2.216 9.02-11.222 9.02-18.694a9.554 9.554 0 0 0-9.554-9.555zm0 13.124a3.57 3.57 0 1 1 3.571-3.57h0a3.57 3.57 0 0 1-3.57 3.57m5.251 8.567l7.71-1.189m-37 5.703l19.82-3.055m-7.412 1.142l-3.74-22.16"
                                stroke-width="4" />
                        </svg>
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
            <div class="grid grid-cols-1 gap-6">
                <!-- Statistics Chart -->
                <div
                    class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-50 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400"
                            viewBox="0 0 16 16">
                            <path fill="currentColor" d="M1.75 13.25V1.5H.5v12a1.24 1.24 0 0 0 1.22 1H15.5v-1.25z" />
                            <path fill="currentColor"
                                d="M3.15 8H4.4v3.9H3.15zm3.26-4h1.26v7.9H6.41zm3.27 2h1.25v5.9H9.68zm3.27-3.5h1.25v9.4h-1.25z" />
                        </svg>
                        Statistik Lokasi per Kategori
                    </h3>

                    <VueApexCharts type="bar" height="300" :options="chartOptions" :series="series"></VueApexCharts>
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