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

provide('darkMode', darkMode)
provide('toggleDarkMode', (value) => {
    darkMode.value = value !== undefined ? value : !darkMode.value
    applyDarkMode()
    localStorage.setItem('theme', darkMode.value ? 'dark' : 'light')
})

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

const toggleDarkMode = (newValue) => {
    darkMode.value = newValue
    localStorage.setItem('theme', darkMode.value ? 'dark' : 'light')
    applyDarkMode()
}

const applyDarkMode = () => {
    document.documentElement.classList.toggle('dark', darkMode.value)
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

// Fetch data function
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
      class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity duration-300"
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
      <div class="brand-section p-4 md:p-5 border-b border-neutral-100 dark:border-neutral-700">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="logo-icon w-8 h-8 md:w-9 md:h-9 rounded-lg flex items-center justify-center">
              <img src="storage/ordinary/logo.png" alt="Logo">
            </div>
            <div>
              <h1 class="text-base md:text-lg font-bold text-neutral-900 dark:text-white">
                Peta Magang
              </h1>
              <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                Temukan lokasi magang & detailnya
              </p>
            </div>
          </div>
          <!-- Tombol tutup sidebar di mobile -->
          <button v-if="isMobile" @click="toggleSidebar"
            class="md:hidden p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700">
            <i class="fas fa-times text-neutral-700 dark:text-neutral-300"></i>
          </button>
        </div>
      </div>

      <!-- Detail Panel -->
      <div class="detail-panel flex-1 p-3 md:p-4 flex flex-col">
        
        <!-- Empty State -->
        <div v-if="!selectedLocation"
          class="empty-card bg-white dark:bg-neutral-800 rounded-xl p-4 border border-neutral-100 dark:border-neutral-700 shadow-sm">
          <div class="flex items-center gap-3">
            <div>
              <div class="flex items-center justify-between">
                <h3 class="font-bold text-neutral-900 dark:text-white text-sm md:text-base">Peta Magang</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                  {{ filteredLocations.length }} lokasi
                </p>
              </div>
              <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                Klik marker atau pilih hasil pencarian untuk melihat detail lokasi.
              </p>
            </div>
          </div>
        </div>

        <!-- Detail Card -->
        <div v-else
          class="detail-card bg-white dark:bg-neutral-800 rounded-xl overflow-hidden shadow-lg border border-neutral-100 dark:border-neutral-700">
          
          <!-- Thumbnail -->
          <div class="relative">
            <img :src="selectedLocation.image_path || 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=400&h=200&fit=crop'"
              :alt="selectedLocation.name_location" class="w-full h-40 md:h-48 object-cover" />
            <!-- Tombol tutup card -->
            <button @click="clearSelection"
              class="absolute top-2 right-2 w-8 h-8 bg-white/90 dark:bg-neutral-800/90 rounded-full flex items-center justify-center shadow-sm hover:bg-white dark:hover:bg-neutral-700 transition-colors">
              <i class="fas fa-times text-neutral-700 dark:text-neutral-300 text-sm"></i>
            </button>
          </div>

          <!-- Content -->
          <div class="p-3 md:p-4">
            <!-- Header dengan judul dan kategori -->
            <div class="mb-3">
              <h2 class="detail-title text-lg md:text-xl font-bold text-neutral-900 dark:text-white mb-1">
                {{ selectedLocation.name_location }}
              </h2>
              <div class="flex items-center justify-between">
                <p class="detail-subtitle text-xs md:text-sm text-neutral-500 dark:text-neutral-400">
                  {{ selectedLocation.category_name }}
                </p>
                <span class="text-xs text-neutral-400 dark:text-neutral-500">
                  {{ formatDate(selectedLocation.created_at) }}
                </span>
              </div>
            </div>

            <!-- Deskripsi -->
            <p class="text-neutral-700 dark:text-neutral-300 text-xs md:text-sm leading-relaxed mb-4 line-clamp-3">
              {{ selectedLocation.description }}
            </p>

            <!-- Koordinat Chip -->
            <div class="mb-4">
              <div
                class="chip bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 text-xs px-3 py-2 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <i class="fas fa-map-marker-alt text-primary"></i>
                  <span>Koordinat</span>
                </div>
                <span class="font-mono text-xs">
                  {{ selectedLocation.latitude.toFixed(6) }}, {{ selectedLocation.longitude.toFixed(6) }}
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons grid grid-cols-2 gap-2 mb-4">
              <button @click="navigateToLocation(selectedLocation)"
                class="btn-primary bg-primary hover:bg-primary-dark text-neutral-900 font-semibold py-2.5 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-route"></i>
                <span class="truncate">Petunjuk Arah</span>
              </button>
              <button @click="openInMaps(selectedLocation.latitude, selectedLocation.longitude)"
                class="btn-secondary border border-neutral-200 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 font-medium py-2.5 px-3 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-location-dot"></i>
                <span class="truncate">Buka di Maps</span>
              </button>
            </div>

            <!-- Additional Info -->
            <div class="additional-info border-t border-neutral-100 dark:border-neutral-700 pt-3 md:pt-4">
              <h4 class="text-xs md:text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2 md:mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle text-info"></i>
                Detail Lokasi
              </h4>
              <div class="space-y-2 text-xs md:text-sm">
                <div class="grid grid-cols-3 gap-2">
                  <span class="text-neutral-600 dark:text-neutral-400 col-span-1">Kategori:</span>
                  <span class="font-medium text-neutral-900 dark:text-white col-span-2 text-right">
                    {{ selectedLocation.category_name }}
                  </span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                  <span class="text-neutral-600 dark:text-neutral-400 col-span-1">Latitude:</span>
                  <span class="font-medium text-neutral-900 dark:text-white col-span-2 text-right font-mono">
                    {{ selectedLocation.latitude.toFixed(8) }}
                  </span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                  <span class="text-neutral-600 dark:text-neutral-400 col-span-1">Longitude:</span>
                  <span class="font-medium text-neutral-900 dark:text-white col-span-2 text-right font-mono">
                    {{ selectedLocation.longitude.toFixed(8) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div id="sidebar-footer" class="sidebar-footer border-t border-neutral-100 dark:border-neutral-700 p-3">
        <div class="flex items-center justify-between">
          <!-- Tambah Lokasi Button -->
          <Link :href="route('request-locations.create')">
            <button
              class="flex items-center gap-2 px-3 py-2 rounded-lg text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-primary hover:bg-opacity-20 dark:hover:bg-primary dark:hover:bg-opacity-20 transition-all duration-200 active:scale-95"
              title="Tambah Lokasi Baru">
              <i class="fas fa-map-marker-alt text-sm"></i>
              <span class="hidden md:inline text-sm font-medium">Tambah Lokasi</span>
            </button>
          </Link>
          
          <!-- Dark Mode Toggle Component -->
          <DarkModeToggle 
            v-model="darkMode" 
            @toggle="toggleDarkMode"
          />
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 relative">
      <!-- Map Component -->
      <MapComponent 
        ref="mapComponentRef"
        :locations="locations"
        :selected-location="selectedLocation"
        :dark-mode="darkMode"
        :sidebar-open="sidebarOpen"
        @location-selected="selectLocation"
        @map-initialized="onMapInitialized"
      >
        <!-- Search Bar Overlay -->
        <div class="flex sm:flex-row md:inline search-container absolute top-3 md:top-4 left-3 md:left-4 right-3 md:right-4 z-40 transition-all duration-200"
          :class="sidebarOpen ? 'opacity-0 pointer-events-none' : 'opacity-100'">
          
          <!-- Mobile Sidebar Toggle Button -->
          <button v-if="isMobile" @click.stop="toggleSidebar"
            class="md:hidden w-10 h-10 absolute bg-primary hover:bg-primary-dark text-neutral-900 rounded-xl shadow-lg">
            <i class="fas fa-bars text-lg w-4 h-4"></i>
          </button>
          
          <div class="search-box relative max-w-2xl max-h-10 mx-auto">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-neutral-400 text-sm md:text-base"></i>
              </div>
              <input v-model="searchQuery" @input="handleSearch" @focus="showAutocomplete = true"
                @keydown.down="highlightNext" @keydown.up="highlightPrev"
                @keydown.enter="selectHighlighted" @keydown.esc="showAutocomplete = false" type="text"
                placeholder="Cari lokasi magang..."
                class="w-full pl-9 md:pl-10 pr-9 md:pr-10 py-2.5 md:py-3 bg-white/90 dark:bg-neutral-800/90 backdrop-blur-sm rounded-xl border border-neutral-200 dark:border-neutral-600 text-neutral-900 dark:text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-transparent shadow-lg text-sm md:text-base" />
              <button v-if="searchQuery" @click="clearSearch"
                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <i class="fas fa-times text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 text-sm md:text-base"></i>
              </button>
            </div>

            <!-- Autocomplete Dropdown -->
            <div v-if="showAutocomplete && filteredResults.length > 0"
              class="autocomplete-dropdown mt-2 bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-600 shadow-2xl overflow-hidden max-h-64 overflow-y-auto">
              <div v-for="(location, index) in filteredResults" :key="location.id_location"
                @click="selectLocation(location)" @mouseenter="highlightedIndex = index" :class="[
                  'autocomplete-item p-2 md:p-3 cursor-pointer transition-colors flex items-center gap-2 md:gap-3 border-b border-neutral-100 dark:border-neutral-700 last:border-b-0',
                  highlightedIndex === index
                    ? 'bg-primary/10 dark:bg-primary/20'
                    : 'hover:bg-neutral-50 dark:hover:bg-neutral-700'
                ]">
                <div class="flex-shrink-0 w-10 h-10 md:w-12 md:h-12 rounded-lg overflow-hidden">
                  <img :src="location.image_path || 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=100&h=100&fit=crop'"
                    :alt="location.name_location" class="w-full h-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-semibold text-neutral-900 dark:text-white truncate text-sm md:text-base">
                    {{ location.name_location }}
                  </h4>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                    {{ location.category_name }} · Klik untuk lihat detail
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Location Indicator (mobile only) -->
        <div v-if="isMobile && selectedLocation && !sidebarOpen" class="absolute w-80 bottom-8 left-4 right-4 z-30">
          <div @click.stop="toggleSidebar"
            class="bg-white dark:bg-neutral-800 rounded-xl p-3 shadow-lg border border-neutral-200 dark:border-neutral-600 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-primary to-primary-dark flex items-center justify-center">
                  <i class="fas fa-map-marker-alt text-white text-xs"></i>
                </div>
                <div class="min-w-0">
                  <h3 class="font-semibold text-neutral-900 dark:text-white truncate text-sm">
                    {{ selectedLocation.name_location }}
                  </h3>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
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