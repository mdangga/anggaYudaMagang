<script setup>
import { ref, onMounted, computed, watch, onUnmounted, inject } from 'vue'
import { Head } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'
import MapComponent from '@/Components/Map.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// Data state
const locations = ref([])
const categories = ref([])
const selectedLocation = ref(null)
const showAutocomplete = ref(false)
const highlightedIndex = ref(-1)
const sidebarOpen = ref(false)
const isMobile = ref(false)

// Map reference
const mapComponentRef = ref(null)
const mapKey = ref(Date.now())

// Methods
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768
}

const selectLocation = (location) => {
    selectedLocation.value = location
    showAutocomplete.value = false
    highlightedIndex.value = -1
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
                            :selected-location="selectedLocation" @location-selected="selectLocation"
                            @map-initialized="onMapInitialized" />
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

<style>
/* Import Leaflet CSS */
@import 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';

/* Fix map container */
.map-container {
    transition: margin-left 0.3s ease;
}

#map {
    z-index: 1;
}

/* Dark mode filter untuk leaflet tiles */
.dark .leaflet-tile-pane {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%) !important;
}

/* Pastikan marker tidak terkena filter */
.dark .leaflet-layer {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%) !important;
}

/* Leaflet overrides */
.leaflet-container {
    font-family: 'Figtree', sans-serif !important;
}

/* Perbaikan untuk performa filter */
.leaflet-tile-container {
    will-change: transform;
}

/* Animasi smooth untuk perubahan theme */
.leaflet-tile-pane {
    transition: filter 0.3s ease !important;
}

.leaflet-control-zoom {
    margin-top: 70px !important;
    border: none !important;
}

.leaflet-control-zoom a {
    width: 36px !important;
    height: 36px !important;
    line-height: 36px !important;
    background-color: white !important;
    color: #374151 !important;
    border: 1px solid #e5e7eb !important;
    font-size: 18px !important;
}

.dark .leaflet-control-zoom a {
    background-color: #374151 !important;
    color: #f3f4f6 !important;
    border-color: #4b5563 !important;
}

.leaflet-control-attribution {
    background-color: rgba(255, 255, 255, 0.9) !important;
    font-size: 11px !important;
    padding: 2px 8px !important;
    border-radius: 4px !important;
    margin-right: 10px !important;
    margin-bottom: 10px !important;
}

.dark .leaflet-control-attribution {
    background-color: rgba(31, 41, 55, 0.9) !important;
    color: #d1d5db !important;
}

.dark .leaflet-control-attribution a {
    color: #93c5fd !important;
}

.leaflet-popup-content {
    margin: 0 !important;
    padding: 0 !important;
}

.leaflet-popup-content-wrapper {
    border-radius: 12px !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    padding: 0 !important;
    overflow: hidden !important;
}

.dark .leaflet-popup-content-wrapper {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
}

.dark .leaflet-popup-tip {
    background: #1f2937 !important;
}

/* Animations */
@keyframes slideIn {
    from {
        transform: translateX(-100%);
    }

    to {
        transform: translateX(0);
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-100%);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .leaflet-control-zoom {
        margin-top: 100px !important;
    }

    .leaflet-control-zoom a {
        width: 32px !important;
        height: 32px !important;
        line-height: 32px !important;
        font-size: 16px !important;
    }
}

/* Fix untuk iOS */
@supports (-webkit-touch-callout: none) {
    .leaflet-container {
        -webkit-tap-highlight-color: transparent;
    }
}
</style>