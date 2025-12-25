<script setup>
/* =========================================================
 * Imports
 * ======================================================= */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'

import MapComponent from '@/Components/Map.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

/* =========================================================
 * State
 * ======================================================= */
const locations = ref([])
const categories = ref([])
const activities = ref([])
const loadingActivities = ref(false)

const selectedLocation = ref(null)
const loadingDetail = ref(false)
const showDetailModal = ref(false)
const showMap = ref(false)

const searchQuery = ref('')
const filteredResults = ref([])
const showAutocomplete = ref(false)
const highlightedIndex = ref(-1)

const isMobile = ref(false)

/* =========================================================
 * Refs
 * ======================================================= */
const mapComponentRef = ref(null)
const mapKey = ref(Date.now())

/* =========================================================
 * Computed
 * ======================================================= */
const filteredLocations = computed(() => {
    if (!searchQuery.value.trim()) return locations.value

    const query = searchQuery.value.toLowerCase()

    return locations.value.filter(location =>
        location.name_location.toLowerCase().includes(query) ||
        location.category.name_category.toLowerCase().includes(query) ||
        location.description.toLowerCase().includes(query)
    )
})

const series = computed(() => [
    {
        name: 'Jumlah Lokasi',
        data: categories.value.map(item => item.locations_count || 0)
    }
])

const recentActivities = computed(() => {
    return [...activities.value]
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 5)
})

const chartOptions = computed(() => {
    const isDark = document.documentElement.classList.contains('dark')
    const textColor = isDark ? '#e2e8f0' : '#374151'
    const gridColor = isDark ? '#334155' : '#e5e7eb'

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
        dataLabels: { enabled: false },
        xaxis: {
            categories: categories.value.map(
                item => item.name_category || 'Unknown'
            ),
            labels: {
                style: { colors: textColor, fontSize: '12px' },
                rotate: -45
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: textColor },
                formatter: val => Math.floor(val)
            },
            title: {
                text: 'Jumlah Lokasi',
                style: { color: textColor }
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4,
            yaxis: { lines: { show: true } }
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: {
                formatter: val => `${val} lokasi`
            }
        },
        colors: [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#8b5cf6'
        ],
        responsive: [
            {
                breakpoint: 640,
                options: {
                    plotOptions: {
                        bar: { columnWidth: '60%' }
                    },
                    xaxis: {
                        labels: { rotate: -90 }
                    }
                }
            }
        ]
    }
})

/* =========================================================
 * Utils
 * ======================================================= */
const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffTime = Math.abs(now - date)
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))

    if (diffDays === 0) {
        return 'Hari ini'
    } else if (diffDays === 1) {
        return 'Kemarin'
    } else if (diffDays < 7) {
        return `${diffDays} hari yang lalu`
    } else {
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        })
    }
}

const formatCoordinates = (lat, lng) => {
    return `${lat.toFixed(6)}, ${lng.toFixed(6)}`
}

const checkMobile = () => {
    isMobile.value = window.innerWidth < 768
}

const invalidateMapSize = () => {
    if (!mapComponentRef.value) return

    clearTimeout(window.mapResizeTimeout)
    window.mapResizeTimeout = setTimeout(() => {
        mapComponentRef.value.invalidateSize()
    }, 250)
}

const onMapInitialized = (mapInstance) => {
    return mapInstance
}

/* =========================================================
 * Search & Autocomplete
 * ======================================================= */
const handleSearch = () => {
    if (!searchQuery.value.trim()) {
        clearSearch()
        return
    }

    filteredResults.value = filteredLocations.value.slice(0, 5)
    showAutocomplete.value = true
    highlightedIndex.value = -1
}

const clearSearch = () => {
    searchQuery.value = ''
    filteredResults.value = []
    showAutocomplete.value = false
    highlightedIndex.value = -1
}

const highlightNext = e => {
    e.preventDefault()
    if (!filteredResults.value.length) return

    highlightedIndex.value =
        (highlightedIndex.value + 1) % filteredResults.value.length
}

const highlightPrev = e => {
    e.preventDefault()
    if (!filteredResults.value.length) return

    highlightedIndex.value =
        highlightedIndex.value <= 0
            ? filteredResults.value.length - 1
            : highlightedIndex.value - 1
}

const selectHighlighted = e => {
    e.preventDefault()
    const item = filteredResults.value[highlightedIndex.value]
    if (item) selectLocation(item)
}

/* =========================================================
 * Location Detail
 * ======================================================= */
const selectLocation = async location => {
    selectedLocation.value = location
    loadingDetail.value = true
    showDetailModal.value = true
    showAutocomplete.value = false
    highlightedIndex.value = -1

    try {
        const res = await fetch(`/locations/get-locations/${location.id_location}`)
        if (!res.ok) throw new Error('Failed to fetch location detail')

        selectedLocation.value = await res.json()
    } catch (err) {
        console.error('Error fetching location detail:', err)
    } finally {
        loadingDetail.value = false
    }
}

const closeDetailModal = () => {
    showDetailModal.value = false
    selectedLocation.value = null
    loadingDetail.value = false

    invalidateMapSize()
}

/* =========================================================
 * API
 * ======================================================= */
async function getLocation() {
    try {
        const res = await fetch('/locations/get-locations')
        if (!res.ok) throw new Error('Fetch failed')

        const data = await res.json()
        locations.value = Array.isArray(data) ? data : []
    } catch (err) {
        console.error('Error loading locations:', err)
        locations.value = []
    }
}

async function getCategories() {
    try {
        const res = await fetch('categories/get-categories')
        if (!res.ok) throw new Error('Fetch failed')

        const data = await res.json()
        categories.value = Array.isArray(data) ? data : []
    } catch (err) {
        console.error('Error loading categories:', err)
        categories.value = []
    }
}

async function getActivities() {
    try {
        loadingActivities.value = true
        const res = await fetch('/locations/ajax')
        if (!res.ok) throw new Error('Fetch activities failed')

        const data = await res.json()
        // Ambil data dari properti "data" jika ada
        activities.value = Array.isArray(data.data) ? data.data : []
    } catch (err) {
        console.error('Error loading activities:', err)
        activities.value = []
    } finally {
        loadingActivities.value = false
    }
}

/* =========================================================
 * Lifecycle
 * ======================================================= */
onMounted(async () => {
    checkMobile()
    await Promise.all([getLocation(), getCategories(), getActivities()])

    requestAnimationFrame(() => {
        showMap.value = true
    })
})

onUnmounted(() => {
    window.removeEventListener('resize', invalidateMapSize)
})
</script>


<template>

    <Head :title="`Dashboard Admin - ${$page.props.profile_web.app_name}`" />

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
                <!-- Header -->
                <div class="flex justify-between p-4 border-b border-gray-200 dark:border-slate-700">
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

                    <!-- Search Input Container -->
                    <div class="relative">
                        <input v-model="searchQuery" @input="handleSearch" @focus="showAutocomplete = true"
                            @keydown.down="highlightNext" @keydown.up="highlightPrev" @keydown.enter="selectHighlighted"
                            @keydown.esc="showAutocomplete = false" type="text" placeholder="Cari lokasi magang..."
                            class="w-full pl-10 pr-9 py-2 bg-white dark:bg-slate-800 rounded-lg border border-neutral-300 dark:border-slate-600 text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-transparent text-sm" />

                        <!-- Search Icon -->
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 text-neutral-500 dark:text-neutral-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Clear Button -->
                        <button v-if="searchQuery" @click="clearSearch"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"
                                class="w-3.5 h-3.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                                fill="currentColor">
                                <path
                                    d="M183.1 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L275.2 320L137.9 457.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l137.3-137.4l137.4 137.3c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L365.8 320l137.3-137.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320.5 274.7z" />
                            </svg>
                        </button>

                        <!-- Autocomplete Dropdown (Position Absolute) -->
                        <div v-if="showAutocomplete && filteredResults.length > 0"
                            class="absolute z-50 top-full left-0 right-0 mt-1 overflow-hidden overflow-y-auto bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-600 shadow-lg max-h-64">
                            <div v-for="(location, index) in filteredResults" :key="location.id_location"
                                @click="selectLocation(location)" @mouseenter="highlightedIndex = index" :class="[
                                    'autocomplete-item p-2 cursor-pointer transition-colors flex items-center gap-2 border-b border-neutral-100 dark:border-neutral-700 last:border-b-0',
                                    highlightedIndex === index
                                        ? 'bg-primary/10 dark:bg-primary/20'
                                        : 'hover:bg-neutral-50 dark:hover:bg-neutral-700'
                                ]">
                                <div class="flex-shrink-0 w-8 h-8 overflow-hidden rounded-md">
                                    <img v-if="location.images && location.images.length"
                                        :src="`/storage/${location.images[0].image_path}`" :alt="location.name_location"
                                        class="object-cover w-full h-full" />
                                    <img v-else
                                        src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=80&h=80&fit=crop"
                                        alt="Default Image" class="object-cover w-full h-full" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-semibold truncate text-neutral-900 dark:text-white">
                                        {{ location.name_location }}
                                    </h4>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                                        {{ location.category.name_category }} · Klik untuk lihat detail
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div class="p-4">
                    <div class="h-96 w-full rounded-md overflow-hidden">
                        <MapComponent :key="mapKey" ref="mapComponentRef" :locations="locations"
                            :selected-location="selectedLocation" @location-selected="selectLocation"
                            @map-initialized="onMapInitialized" />
                    </div>
                </div>
            </div>


            <div class="grid lg:grid-cols-11 gap-6 mb-6">
                <!-- Statistics Chart -->
                <div
                    class="lg:col-span-7 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5">
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

                <!-- Recent Activities Section -->
                <div
                    class="lg:col-span-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                    <!-- Header dengan gradient -->
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-10 h-10">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru
                                    </h3>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button @click="getActivities" :disabled="loadingActivities"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="loadingActivities" class="animate-spin h-3 w-3 mr-1.5"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" fill="none" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <span>{{ loadingActivities ? 'Memuat...' : 'Refresh' }}</span>
                                </button>

                                <a :href="route('locations.index')"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                                    Lihat Semua
                                    <svg class="w-3 h-3 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loadingActivities" class="p-8 text-center">
                        <div class="inline-flex flex-col items-center">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full border-2 border-gray-200 dark:border-gray-700"></div>
                                <div
                                    class="absolute top-0 left-0 w-12 h-12 rounded-full border-2 border-green-500 border-t-transparent animate-spin">
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">Memuat aktivitas terbaru...</p>
                        </div>
                    </div>

                    <!-- Activities List -->
                    <div v-else-if="recentActivities.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="activity in recentActivities" :key="activity.id_location"
                            @click="selectLocation(activity)"
                            class="group px-6 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0">

                            <div class="flex items-center gap-3">
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4
                                            class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-1">
                                            {{ activity.name_location }}
                                        </h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap ml-2">
                                            {{ formatDate(activity.created_at) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                        <span class="font-medium">{{ activity.student_name }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{
                                            activity.category.name_category
                                        }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{
                                            activity.department.degree_level
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center">
                        <div class="inline-flex flex-col items-center max-w-xs mx-auto">
                            <div
                                class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Belum ada aktivitas
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Tidak ada aktivitas magang terbaru
                                yang
                                ditemukan</p>
                            <button @click="getActivities"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refresh Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail Lokasi -->
        <div v-if="showDetailModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div
                class="bg-white dark:bg-neutral-800 rounded-lg shadow-2xl max-w-4xl w-full max-h-[95vh] flex flex-col overflow-hidden transform transition-all duration-300">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex-shrink-0">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-50">
                            Detail Lokasi Magang
                        </h3>
                        <button @click="closeDetailModal"
                            class="text-neutral-400 dark:text-neutral-300 hover:text-neutral-500 dark:hover:text-neutral-200 transition-colors p-1 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-700"
                            :disabled="loadingDetail">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content - Loading State -->
                <div v-if="loadingDetail" class="overflow-y-auto p-6 flex-grow flex items-center justify-center">
                    <div class="text-center">
                        <div
                            class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mb-4">
                        </div>
                        <p class="text-neutral-600 dark:text-neutral-300 font-medium">
                            Memuat data detail lokasi...
                        </p>
                    </div>
                </div>

                <!-- Modal Content - Data Detail -->
                <div v-else-if="selectedLocation && selectedLocation.id_location"
                    class="overflow-y-auto p-6 space-y-8 flex-grow">
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

                        <div v-if="selectedLocation.images && selectedLocation.images.length"
                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <div v-for="image in selectedLocation.images" :key="image.id_image"
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
                                                selectedLocation.student_name }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">NIM</label>
                                            <p class="text-sm text-neutral-900 dark:text-neutral-50 font-mono">{{
                                                selectedLocation.nim }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Fakultas</label>
                                            <p class="text-sm text-neutral-900 dark:text-neutral-50">{{
                                                selectedLocation.department?.faculty?.name_faculty }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Jurusan</label>
                                            <p class="text-sm text-neutral-900 dark:text-neutral-50">
                                                {{ selectedLocation.department?.name_department }}
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400 ml-2">({{
                                                    selectedLocation.department?.degree_level }})</span>
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
                                            <span v-if="selectedLocation.approved_at"
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
                                    <div v-if="selectedLocation.approved_at"
                                        class="pt-2 border-t border-gray-200 dark:border-neutral-600">
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Disetujui
                                            pada</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50">
                                            {{ new Date(selectedLocation.approved_at).toLocaleDateString('id-ID', {
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
                                            selectedLocation.name_location }}</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Deskripsi</label>
                                        <div class="mt-1">
                                            <p
                                                class="text-sm text-neutral-900 dark:text-neutral-50 leading-relaxed whitespace-pre-line max-h-32 overflow-y-auto pr-2">
                                                {{ selectedLocation.description || 'Tidak ada deskripsi' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Kontak</label>
                                        <p class="text-sm text-neutral-900 dark:text-neutral-50">{{
                                            selectedLocation.contact ||
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
                                                {{ selectedLocation.latitude }}</p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1">Longitude</label>
                                            <p
                                                class="text-sm text-neutral-900 dark:text-neutral-50 font-mono bg-neutral-100 dark:bg-neutral-800 p-2 rounded">
                                                {{ selectedLocation.longitude }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-3 border-t border-gray-200 dark:border-neutral-600">
                                        <a :href="`https://www.google.com/maps/search/?api=1&query=${selectedLocation.latitude},${selectedLocation.longitude}`"
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
                    <div v-if="selectedLocation.created_at"
                        class="pt-6 border-t border-gray-200 dark:border-neutral-700">
                        <h4
                            class="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4 flex items-center gap-2">
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
                                        {{ new Date(selectedLocation.created_at).toLocaleDateString('id-ID', {
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

                            <div v-if="selectedLocation.approved_at" class="relative">
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
                                        {{ new Date(selectedLocation.approved_at).toLocaleDateString('id-ID', {
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
                <div
                    class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700 flex justify-end flex-shrink-0 gap-3">
                    <button @click="closeDetailModal"
                        class="px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium transition-colors shadow-md"
                        :disabled="loadingDetail">
                        Tutup
                    </button>
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