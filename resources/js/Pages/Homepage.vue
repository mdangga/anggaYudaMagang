<script setup>
import { ref, onMounted, computed, watch, onUnmounted, nextTick, provide } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DarkModeToggle from '@/Components/DarkModeToggle.vue'
import MapComponent from '@/Components/Map.vue'

// Data state
const locations = ref([])
const selectedLocation = ref(null)
const searchQuery = ref('')
const filteredResults = ref([])
const showAutocomplete = ref(false)
const highlightedIndex = ref(-1)
const darkMode = ref(false)
const sidebarOpen = ref(false)
const isMobile = ref(false)
const mapComponentRef = ref(null)
const currentImageIndex = ref(0)
const autoSlideInterval = ref(null)
const selectedCategoryIds = ref([])

provide('darkMode', darkMode)
provide('toggleDarkMode', (value) => {
    darkMode.value = value !== undefined ? value : !darkMode.value
    applyDarkMode()
    localStorage.setItem('theme', darkMode.value ? 'dark' : 'light')
})

// Computed
const categories = computed(() => {
    const map = new Map()
    locations.value.forEach(loc => {
        if (loc.category) {
            map.set(loc.category.id_category, loc.category)
        }
    })
    return Array.from(map.values())
})

const filteredLocations = computed(() => {
    let result = locations.value

    // ===============================
    // FILTER KATEGORI (MULTI CHECKBOX)
    // ===============================
    if (selectedCategoryIds.value.length > 0) {
        result = result.filter(loc =>
            selectedCategoryIds.value.includes(
                loc.category?.id_category
            )
        )
    }

    // ===============================
    // FILTER SEARCH
    // ===============================
    const query = searchQuery.value?.trim().toLowerCase()
    if (query) {
        result = result.filter(loc =>
            loc.name_location?.toLowerCase().includes(query) ||
            loc.description?.toLowerCase().includes(query) ||
            loc.category?.name_category?.toLowerCase().includes(query)
        )
    }

    return result
})


// Methods
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768
}

const toggleDarkMode = (newValue) => {
    darkMode.value = newValue
    localStorage.setItem('theme', darkMode.value ? 'dark' : 'light')
    applyDarkMode()
}

const applyDarkMode = () => {
    document.documentElement.classList.toggle('dark', darkMode.value)
}

const selectLocation = async (location) => {
    selectedLocation.value = location;
    showAutocomplete.value = false;
    highlightedIndex.value = -1;
    currentImageIndex.value = 0;

    if (isMobile.value) sidebarOpen.value = true;

    const footer = document.getElementById('sidebar-footer');
    if (footer) footer.classList.add('hidden');

    try {
        console.log('Fetching location detail for ID:', location.id_location);
        const response = await fetch(`/locations/get-locations/${location.id_location}`);
        if (!response.ok) throw new Error('Failed to fetch location detail');
        const data = await response.json();
        console.log('Location detail fetched:', data);
        selectedLocation.value = data;

        if (data.images && data.images.length > 1) {
            setTimeout(() => {
                startAutoSlide()
            }, 1000)
        }
    } catch (error) {
        console.error('Error fetching location detail:', error);
    }
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

const toggleCategory = (id) => {
    const index = selectedCategoryIds.value.indexOf(id)
    if (index === -1) {
        selectedCategoryIds.value.push(id)
    } else {
        selectedCategoryIds.value.splice(index, 1)
    }
}

const clearCategories = () => {
    selectedCategoryIds.value = []
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
        month: 'short',
        year: 'numeric'
    })
}
const navigateToLocation = (location) => {
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${location.latitude},${location.longitude}`, '_blank')
}

const openInMaps = (lat, lng) => {
    window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, '_blank')
}

const openWhatsApp = (phone, locationName) => {
    const message = encodeURIComponent(
        `Halo, saya ingin bertanya tentang lokasi magang ${locationName}.`
    )

    const url = `https://wa.me/${phone}?text=${message}`
    window.open(url, '_blank')
}

const nextImage = () => {
    if (selectedLocation.value?.images?.length) {
        currentImageIndex.value = (currentImageIndex.value + 1) % selectedLocation.value.images.length
    }
}

const prevImage = () => {
    if (selectedLocation.value?.images?.length) {
        currentImageIndex.value = currentImageIndex.value <= 0
            ? selectedLocation.value.images.length - 1
            : currentImageIndex.value - 1
    }
}

const goToImage = (index) => {
    currentImageIndex.value = index
    resetAutoSlide()
}

const startAutoSlide = () => {
    if (selectedLocation.value?.images?.length > 1) {
        stopAutoSlide()
        autoSlideInterval.value = setInterval(() => {
            nextImage()
        }, 5000)
    }
}

const stopAutoSlide = () => {
    if (autoSlideInterval.value) {
        clearInterval(autoSlideInterval.value)
        autoSlideInterval.value = null
    }
}

const resetAutoSlide = () => {
    stopAutoSlide()
    startAutoSlide()
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

// Fetch data function
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

// Map event handler
const onMapInitialized = (mapInstance) => {
    return mapInstance
}

// Lifecycle
onMounted(async () => {
    checkMobile()

    // Setup dark mode
    const saved = localStorage.getItem('theme')
    const mq = window.matchMedia('(prefers-color-scheme: dark)')

    if (saved) {
        darkMode.value = saved === 'dark'
    } else {
        darkMode.value = mq.matches
        if (mq.addEventListener) {
            mq.addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    darkMode.value = e.matches
                    applyDarkMode()
                }
            })
        }
    }
    applyDarkMode()

    // Fetch locations
    await getLocation()

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
    stopAutoSlide()
})

// Watchers
watch(sidebarOpen, (newValue) => {
    if (mapComponentRef.value) {
        setTimeout(() => {
            mapComponentRef.value.invalidateSize()
        }, 300)
    }
})

watch(selectedLocation, (newLocation) => {
    if (!newLocation) {
        currentImageIndex.value = 0
        stopAutoSlide()
    } else if (newLocation?.images?.length > 1) {
        // Start autoslide ketika ada lebih dari 1 gambar
        setTimeout(() => {
            startAutoSlide()
        }, 1000)
    }

    if (!newLocation && sidebarOpen.value && isMobile.value) {
        sidebarOpen.value = false
    }
})
</script>

<template>

    <Head title="Home" />
    <div
        :class="['h-screen flex flex-col md:flex-row overflow-hidden', darkMode ? 'dark bg-neutral-900' : 'bg-neutral-50']">

        <!-- Overlay untuk mobile saat sidebar terbuka -->
        <div v-if="isMobile && sidebarOpen" @click="toggleSidebar"
            class="fixed inset-0 z-40 transition-opacity duration-300 bg-black/50 md:hidden"
            :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'"></div>

        <!-- Sidebar -->
        <aside id="sidebar" :class="[
            'fixed md:static z-50 transition-all duration-300 ease-in-out',
            'w-full md:w-96 lg:w-[360px]',
            'bg-white dark:bg-neutral-800',
            'border-t md:border-r border-neutral-200 dark:border-neutral-700',
            'shadow-2xl md:shadow-lg',
            isMobile ? [
                'bottom-0 left-0 right-0',
                'rounded-t-2xl md:rounded-none',
                'h-[85vh] md:h-full',
                'max-h-[85vh] md:max-h-none',
                sidebarOpen ? 'translate-y-0' : 'translate-y-full'
            ] : [
                'h-full',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]
        ]">

            <!-- Header/Brand -->
            <div class="p-4 border-b brand-section md:p-5 border-neutral-100 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg logo-icon md:w-9 md:h-9">
                            <img src="storage/ordinary/logo.png" alt="Logo">
                        </div>
                        <div>
                            <h1 class="text-base font-bold md:text-lg text-neutral-900 dark:text-white">
                                Peta Magang
                            </h1>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                                Temukan lokasi magang & detailnya
                            </p>
                        </div>
                    </div>
                    <!-- Tombol tutup sidebar di mobile -->
                    <button v-if="isMobile" @click="clearSelection"
                        class="p-2 rounded-lg md:hidden hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"
                            class="w-5 h-5 text-neutral-500 dark:text-neutral-300" fill="currentColor">
                            <path
                                d="M183.1 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L275.2 320L137.9 457.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l137.3-137.4l137.4 137.3c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L365.8 320l137.3-137.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320.5 274.7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Detail Panel -->
            <div class="flex flex-col flex-1 detail-panel" :class="{ 'p-4': isMobile }">

                <!-- Empty State -->
                <div v-if="!selectedLocation"
                    class="p-4 bg-white border shadow-sm empty-card dark:bg-neutral-800 rounded-xl border-neutral-100 dark:border-neutral-700 md:m-4"
                    :class="{ 'm-4': isMobile }">
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-neutral-900 dark:text-white md:text-base">Peta Magang
                                </h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ filteredLocations.length }} lokasi
                                </p>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Klik marker atau pilih hasil pencarian untuk melihat detail lokasi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Detail Card -->
                <div v-else class="bg-white detail-card dark:bg-neutral-800"
                    :class="{ ' rounded-xl shadow-md border border-neutral-100 dark:border-neutral-700': isMobile }">

                    <div v-if="selectedLocation"
                        class="relative overflow-hidden group h-[235px] bg-neutral-100 dark:bg-neutral-700">
                        <!-- Carousel Container -->
                        <div class="relative w-full h-full">
                            <!-- Images Container dengan animasi -->
                            <div class="relative flex w-full h-full transition-transform duration-300 ease-in-out"
                                :style="{ transform: `translateX(-${currentImageIndex * 100}%)` }">
                                <div v-for="(image, index) in selectedLocation.images" :key="image.id_image"
                                    class="flex-shrink-0 w-full h-full">
                                    <img :src="`/storage/${image.image_path}`"
                                        :alt="image.alt_text || selectedLocation.name_location"
                                        class="object-cover w-full h-full" />
                                </div>

                                <!-- Fallback jika tidak ada gambar -->
                                <div v-if="!selectedLocation.images || selectedLocation.images.length === 0"
                                    class="flex items-center justify-center flex-shrink-0 w-full h-full bg-gradient-to-br from-neutral-200 to-neutral-300 dark:from-neutral-700 dark:to-neutral-800">
                                    <svg class="w-16 h-16 text-neutral-400 dark:text-neutral-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Navigation Areas - pause saat hover -->
                            <div v-if="selectedLocation.images && selectedLocation.images.length > 1"
                                class="absolute inset-0 flex" @mouseenter="stopAutoSlide" @mouseleave="startAutoSlide">
                                <!-- Left Click Area (25% width) -->
                                <div @click.stop="prevImage"
                                    class="relative w-1/4 h-full transition-all duration-200 cursor-pointer group/left hover:bg-gradient-to-r from-black/20 to-transparent">
                                    <!-- Left SVG Icon -->
                                    <div class="absolute transform -translate-y-1/2 left-2 top-1/2">
                                        <svg class="w-5 h-5 text-white transition-opacity duration-200 opacity-0 group-hover/left:opacity-100"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Middle Area (50% width) -->
                                <div class="w-1/2 h-full"></div>

                                <!-- Right Click Area (25% width) -->
                                <div @click.stop="nextImage"
                                    class="relative w-1/4 h-full transition-all duration-200 cursor-pointer group/right hover:bg-gradient-to-l from-black/20 to-transparent">
                                    <!-- Right SVG Icon -->
                                    <div class="absolute transform -translate-y-1/2 right-2 top-1/2">
                                        <svg class="w-5 h-5 text-white transition-opacity duration-200 opacity-0 group-hover/right:opacity-100"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Counter -->
                        <div v-if="selectedLocation.images && selectedLocation.images.length > 1"
                            class="absolute px-2 py-1 text-xs rounded-full text-neutral-700 dark:text-neutral-100 bottom-2 right-2 bg-white/90 dark:bg-neutral-800/90">
                            {{ currentImageIndex + 1 }} / {{ selectedLocation.images.length }}
                        </div>

                        <!-- Tombol tutup card -->
                        <button @click="clearSelection"
                            class="absolute flex items-center justify-center w-8 h-8 transition-colors rounded-full shadow-sm top-2 right-2 bg-white/90 dark:bg-neutral-800/90 hover:bg-white dark:hover:bg-neutral-700"
                            :class="{ 'hidden': isMobile }">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"
                                class="w-4 h-4 text-neutral-700 dark:text-neutral-300" fill="currentColor">
                                <path
                                    d="M183.1 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L275.2 320L137.9 457.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l137.3-137.4l137.4 137.3c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L365.8 320l137.3-137.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320.5 274.7z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="relative">
                        <!-- Header -->
                        <div class="px-3 pt-3 mb-3 md:px-4 md:pt-4">
                            <h2 class="mb-1 text-lg font-bold detail-title md:text-xl text-neutral-900 dark:text-white">
                                {{ selectedLocation.name_location }}
                            </h2>
                            <div class="flex items-center justify-between">
                                <p class="text-xs detail-subtitle md:text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ selectedLocation.category.name_category }}
                                </p>
                                <!-- <span class="text-xs text-neutral-400 dark:text-neutral-500">
                  {{ formatDate(selectedLocation.created_at) }}
                </span> -->
                            </div>
                        </div>
                        <!-- separator -->
                        <div class="w-full h-px bg-neutral-100 dark:bg-neutral-600/35"></div>

                        <div class="px-3 pt-3 pb-3 md:px-4 md:pb-4">
                            <!-- Deskripsi -->
                            <p
                                class="mb-4 text-xs leading-relaxed text-neutral-700 dark:text-neutral-300 md:text-sm line-clamp-3">
                                {{ selectedLocation.description }}
                            </p>
                            <p class="mb-4 text-xs text-neutral-700 dark:text-neutral-300 md:text-sm">
                                {{ selectedLocation.department?.name_department }}
                                <span class="mx-1">•</span>
                                {{ selectedLocation.department?.faculty?.name_faculty }}
                            </p>
                            <!-- Koordinat Chip -->
                            <div class="mb-4">
                                <div
                                    class="flex items-center justify-between px-3 py-2 text-xs rounded-lg chip bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3 h-3 text-primary-600" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 1024 1536">
                                            <path fill="currentColor"
                                                d="M768 512q0-106-75-181t-181-75t-181 75t-75 181t75 181t181 75t181-75t75-181m256 0q0 109-33 179l-364 774q-16 33-47.5 52t-67.5 19t-67.5-19t-46.5-52L33 691Q0 621 0 512q0-212 150-362T512 0t362 150t150 362" />
                                        </svg>
                                        <span>Koordinat</span>
                                    </div>
                                    <span class="font-mono text-xs">
                                        {{ selectedLocation.latitude.toFixed(6) }}, {{
                                            selectedLocation.longitude.toFixed(6) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-3 gap-2 mb-4 action-buttons">
                                <button @click="openWhatsApp(selectedLocation.contact, selectedLocation.name_location)"
                                    :disabled="!selectedLocation.contact" class="btn-primary font-semibold py-2.5 px-3 rounded-lg transition-all duration-200
              flex items-center justify-center gap-2 text-sm
              bg-primary hover:bg-primary-dark text-neutral-700
              disabled:bg-neutral-300 disabled:text-neutral-500
              disabled:cursor-not-allowed disabled:hover:bg-neutral-300">

                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2c-5.46 0-9.91 4.45-9.91 9.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91c0-2.65-1.03-5.14-2.9-7.01m-7.01 15.24c-1.48 0-2.93-.4-4.2-1.15l-.3-.18l-3.12.82l.83-3.04l-.2-.31a8.26 8.26 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24c2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.02 4.54-3.68 8.23-8.22 8.23m4.52-6.16c-.25-.12-1.47-.72-1.69-.81c-.23-.08-.39-.12-.56.12c-.17.25-.64.81-.78.97c-.14.17-.29.19-.54.06c-.25-.12-1.05-.39-1.99-1.23c-.74-.66-1.23-1.47-1.38-1.72c-.14-.25-.02-.38.11-.51c.11-.11.25-.29.37-.43s.17-.25.25-.41c.08-.17.04-.31-.02-.43s-.56-1.34-.76-1.84c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31c-.22.25-.86.85-.86 2.07s.89 2.4 1.01 2.56c.12.17 1.75 2.67 4.23 3.74c.59.26 1.05.41 1.41.52c.59.19 1.13.16 1.56.1c.48-.07 1.47-.6 1.67-1.18c.21-.58.21-1.07.14-1.18s-.22-.16-.47-.28" />
                                    </svg>
                                    <span class="truncate">Chat</span>
                                </button>
                                <button @click="navigateToLocation(selectedLocation)"
                                    class="btn-secondary border border-neutral-200 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 font-medium py-2.5 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">

                                        <g fill="none" stroke="currentColor" stroke-width="1">
                                            <path stroke-width="1.5"
                                                d="M18.719 10.715a1.044 1.044 0 0 1-1.437 0c-1.765-1.683-4.13-3.564-2.977-6.294C14.929 2.945 16.425 2 18 2s3.072.945 3.695 2.42c1.152 2.728-1.207 4.617-2.977 6.295Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18 6h.009" />
                                            <circle cx="5" cy="19" r="3" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="1.5" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 7H9.5C7.567 7 6 8.343 6 10s1.567 3 3.5 3h3c1.933 0 3.5 1.343 3.5 3s-1.567 3-3.5 3H11" />
                                        </g>
                                    </svg>
                                    <span class="truncate">Rute</span>
                                </button>
                                <button @click="openInMaps(selectedLocation.latitude, selectedLocation.longitude)"
                                    class="btn-secondary border border-neutral-200 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 font-medium py-2.5 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36">
                                        <path fill="currentColor"
                                            d="M18 6.72a5.73 5.73 0 1 0 5.73 5.73A5.73 5.73 0 0 0 18 6.72m0 9.46a3.73 3.73 0 1 1 3.73-3.73A3.73 3.73 0 0 1 18 16.17Z"
                                            class="clr-i-outline clr-i-outline-path-1" />
                                        <path fill="currentColor"
                                            d="M18 2A11.79 11.79 0 0 0 6.22 13.73c0 4.67 2.62 8.58 4.54 11.43l.35.52a100 100 0 0 0 6.14 8l.76.89l.76-.89a100 100 0 0 0 6.14-8l.35-.53c1.91-2.85 4.53-6.75 4.53-11.42A11.79 11.79 0 0 0 18 2m5.59 22l-.36.53c-1.72 2.58-4 5.47-5.23 6.9c-1.18-1.43-3.51-4.32-5.23-6.9l-.35-.53c-1.77-2.64-4.2-6.25-4.2-10.31a9.78 9.78 0 1 1 19.56 0c0 4.1-2.42 7.71-4.19 10.31"
                                            class="clr-i-outline clr-i-outline-path-2" />
                                        <path fill="none" d="M0 0h36v36H0z" />
                                    </svg>
                                    <span class="truncate">Maps</span>
                                </button>

                            </div>

                            <div
                                class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                                <span>
                                    Ditambahkan oleh <span
                                        class="font-medium truncate text-neutral-700 dark:text-neutral-300">
                                        {{ selectedLocation.student_name }}
                                    </span>
                                </span>
                                <span>
                                    {{ formatDate(selectedLocation.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div id="sidebar-footer" class="p-3 border-t sidebar-footer border-neutral-100 dark:border-neutral-700">
                <div class="flex items-center justify-between">

                    <p class="flex items-center gap-2 px-3 py-2 text-xs text-neutral-400">
                        &copy; 2025 Angga & Yuda IF23B.
                    </p>
                    <!-- Dark Mode Toggle Component -->
                    <DarkModeToggle v-model="darkMode" @toggle="toggleDarkMode" />
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="relative flex-1">
            <!-- Map Component -->
            <MapComponent ref="mapComponentRef" :locations="filteredLocations" :selected-location="selectedLocation"
                :dark-mode="darkMode" :sidebar-open="sidebarOpen" @location-selected="selectLocation"
                @map-initialized="onMapInitialized">

                <div class="absolute z-40 flex transition-all duration-200 sm:flex-row md:inline search-container top-3 md:top-4 left-3 md:left-4 right-3 md:right-4"
                    :class="sidebarOpen ? 'opacity-0 pointer-events-none' : 'opacity-100'">

                    <!-- Mobile Sidebar Toggle Button -->
                    <button v-if="isMobile" @click.stop="toggleSidebar"
                        class="absolute flex items-center justify-center w-10 h-10 shadow-lg md:hidden bg-primary hover:bg-primary-dark text-neutral-700 rounded-xl">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                            <path fill="currentColor"
                                d="M96 160c0-17.7 14.3-32 32-32h384c17.7 0 32 14.3 32 32s-14.3 32-32 32H128c-17.7 0-32-14.3-32-32m0 160c0-17.7 14.3-32 32-32h384c17.7 0 32 14.3 32 32s-14.3 32-32 32H128c-17.7 0-32-14.3-32-32m448 160c0 17.7-14.3 32-32 32H128c-17.7 0-32-14.3-32-32s14.3-32 32-32h384c17.7 0 32 14.3 32 32" />
                        </svg>
                    </button>

                    <div class="relative max-w-2xl mx-auto search-box max-h-10">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <svg class="z-20 w-4 h-4 text-neutral-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 640 640">
                                    <path fill="currentColor"
                                        d="M480 272c0 45.9-14.9 88.3-40 122.7l126.6 126.7c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L394.7 440c-34.4 25.1-76.8 40-122.7 40c-114.9 0-208-93.1-208-208S157.1 64 272 64s208 93.1 208 208M272 416c79.5 0 144-64.5 144-144s-64.5-144-144-144s-144 64.5-144 144s64.5 144 144 144" />
                                </svg>
                            </div>
                            <!-- INPUT -->
                            <input v-model="searchQuery" @input="handleSearch" @focus="showAutocomplete = true"
                                @keydown.down="highlightNext" @keydown.up="highlightPrev"
                                @keydown.enter="selectHighlighted" @keydown.esc="showAutocomplete = false" type="text"
                                placeholder="Cari atau filter lokasi magang..." class="w-full pl-9 md:pl-10 pr-28 md:pr-32 py-2.5 md:py-3
           bg-white/90 dark:bg-neutral-800/90 backdrop-blur-sm
           rounded-xl border border-neutral-200 dark:border-neutral-600
           text-neutral-900 dark:text-white placeholder-neutral-500
           focus:outline-none focus:ring-2 focus:ring-primary/50
           focus:border-transparent shadow-lg text-sm md:text-base" />

                            <!-- INFO FILTER AKTIF -->
                            <div v-if="selectedCategoryIds.length"
                                class="absolute inset-y-0 flex items-center right-10">
                                <div class="flex items-center gap-1 px-2 py-0.5 text-xs rounded-full
             bg-primary-600/10 text-neutral-700 border border-primary/30">
                                    <span>{{ selectedCategoryIds.length }} filter</span>
                                    <button @click.stop="clearCategories" class="hover:text-red-500">
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <button v-if="searchQuery" @click="clearSearch"
                                class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"
                                    class="w-4 h-4 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 md:text-base"
                                    fill="currentColor">
                                    <path
                                        d="M183.1 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L275.2 320L137.9 457.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l137.3-137.4l137.4 137.3c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L365.8 320l137.3-137.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320.5 274.7z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Autocomplete Dropdown -->
                        <div v-if="showAutocomplete"
                            class="mt-2 overflow-hidden bg-white border shadow-2xl dark:bg-neutral-800 rounded-xl border-neutral-200 dark:border-neutral-600">

                            <!-- FILTER KATEGORI (DI ATAS AUTOCOMPLETE) -->
                            <div class="px-3 py-2 border-b dark:border-neutral-700">
                                <div class="flex flex-wrap gap-2">

                                    <!-- Semua -->
                                    <button @click="clearCategories" :class="[
                                        'px-3 py-1 text-xs rounded-full border transition',
                                        selectedCategoryIds.length === 0
                                            ? 'bg-primary-600/70 text-white border-primary/25 dark:border-primary-700'
                                            : 'bg-white dark:text-neutral-300 dark:bg-neutral-700 border-neutral-300 dark:border-neutral-600'
                                    ]">
                                        Semua
                                    </button>

                                    <!-- Pills kategori -->
                                    <button v-for="cat in categories" :key="cat.id_category"
                                        @click="toggleCategory(cat.id_category)" :class="[
                                            'px-3 py-1 text-xs rounded-full border transition',
                                            selectedCategoryIds.includes(cat.id_category)
                                                ? 'bg-primary-600/70 text-white border-primary/25 dark:border-primary-700'
                                                : 'bg-white dark:text-neutral-300 dark:bg-neutral-700 border-neutral-300 dark:border-neutral-600'
                                        ]">
                                        {{ cat.name_category }}
                                    </button>
                                </div>
                            </div>

                            <!-- HASIL AUTOCOMPLETE -->
                            <div v-if="filteredResults.length" class="overflow-y-auto max-h-64">

                                <div v-for="(location, index) in filteredResults" :key="location.id_location"
                                    @click="selectLocation(location)" @mouseenter="highlightedIndex = index" :class="[
                                        'p-3 cursor-pointer flex gap-3 border-b dark:border-neutral-700 last:border-b-0',
                                        highlightedIndex === index
                                            ? 'bg-primary/10'
                                            : 'hover:bg-neutral-50 dark:hover:bg-neutral-700'
                                    ]">

                                    <div class="w-10 h-10 overflow-hidden rounded-lg">
                                        <img v-if="location.images?.length"
                                            :src="`/storage/${location.images[0].image_path}`"
                                            class="object-cover w-full h-full" />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold truncate dark:text-neutral-300">
                                            {{ location.name_location }}
                                        </h4>
                                        <p class="text-xs text-neutral-500">
                                            {{ location.category.name_category }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- EMPTY -->
                            <div v-else class="px-4 py-6 text-sm text-center text-neutral-400">
                                Tidak ada hasil
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Location Indicator (mobile only) -->
                <div v-if="isMobile && selectedLocation && !sidebarOpen"
                    class="absolute z-30 w-80 bottom-10 left-4 right-4">
                    <div @click.stop="toggleSidebar"
                        class="p-3 transition-colors bg-white border shadow-lg cursor-pointer dark:bg-neutral-800 rounded-xl border-neutral-200 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-r from-primary to-primary-dark">
                                    <i class="text-xs text-white fas fa-map-marker-alt"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold truncate text-neutral-900 dark:text-white">
                                        {{ selectedLocation.name_location }}
                                    </h3>
                                    <p class="text-xs truncate text-neutral-500 dark:text-neutral-400">
                                        {{ selectedLocation.category_name }}
                                    </p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-neutral-400"></i>
                        </div>
                    </div>
                </div>
            </MapComponent>
        </main>
    </div>
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

.leaflet-popup-content p {
    margin: 0 !important;
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

/* Custom scrollbar */
.detail-panel::-webkit-scrollbar {
    width: 4px;
}

.detail-panel::-webkit-scrollbar-track {
    background: transparent;
}

.detail-panel::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 2px;
}

.dark .detail-panel::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
}

.leaflet-control-attribution.leaflet-control {
    margin-left: 10px;
}

/* carousel */
.carousel-slide {
    transition: transform 0.3s ease-in-out;
}

.carousel-dot {
    transition: all 0.3s ease;
}

.carousel-button {
    transition: all 0.2s ease;
}

.carousel-button:hover {
    transform: scale(1.1);
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
    .search-container {
        left: 12px;
        right: 12px;
    }

    .leaflet-control-zoom {
        margin-top: 100px !important;
    }

    .leaflet-control-zoom a {
        width: 32px !important;
        height: 32px !important;
        line-height: 32px !important;
        font-size: 16px !important;
    }

    /* Improve touch targets */
    .autocomplete-item {
        padding: 12px !important;
    }

    .btn-primary,
    .btn-secondary {
        padding: 10px 12px !important;
        font-size: 13px !important;
    }

    /* Better mobile card spacing */
    .detail-card {
        margin-bottom: 20px;
    }
}

/* Mobile bottom sheet effect */
@media (max-width: 768px) {
    #sidebar {
        max-height: 85vh;
        top: auto;
        bottom: 0;
        border-radius: 20px 20px 0 0;
        border-right: none;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    /* Make detail content scrollable inside the sidebar on mobile */
    .detail-panel {
        display: flex;
        flex-direction: column;
        padding-bottom: 0.5rem;
    }

    .detail-card {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        max-height: calc(85vh - 120px);
    }

    .empty-card {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
    }

    .dark #sidebar {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Handle for mobile bottom sheet */
    #sidebar::before {
        content: '';
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 4px;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 2px;
    }

    .dark #sidebar::before {
        background: rgba(255, 255, 255, 0.1);
    }
}

/* Smooth transitions */
.sidebar-footer,
.brand-section {
    transition: all 0.3s ease;
}

/* Fix untuk iOS */
@supports (-webkit-touch-callout: none) {
    .leaflet-container {
        -webkit-tap-highlight-color: transparent;
    }
}
</style>
