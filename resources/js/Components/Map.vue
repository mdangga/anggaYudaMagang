<template>
  <div class="map-container h-full relative">
    <div ref="mapContainer" id="map" class="absolute inset-0 z-0"></div>
    <slot></slot>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick, inject } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  locations: {
    type: Array,
    required: true,
    default: () => []
  },
  selectedLocation: {
    type: Object,
    default: null
  },
  sidebarOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['location-selected', 'map-initialized'])

// Inject dark mode from parent layout
const darkMode = inject('darkMode', ref(false))

// Map references
const mapContainer = ref(null)
let map = null
let tiles = null
const markers = ref({})
const mapInitialized = ref(false)

// Methods
const createCustomIcon = (isSelected = false) => {
  return L.divIcon({
    html: `
      <div class="relative transition-transform duration-200">
        <div class="text-primary shadow-lg flex items-center justify-center transform transition-all duration-300 hover:scale-110">
          <i class="fas fa-map-marker-alt text-primary text-3xl"></i>
        </div>
        ${isSelected ? '<div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"></div>' : ''}
      </div>
    `,
    className: 'custom-marker',
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40]
  })
}

const addMarkers = () => {
  if (!map) return

  // Clear existing markers
  Object.values(markers.value).forEach(marker => {
    if (marker && map) map.removeLayer(marker)
  })
  markers.value = {}

  // Add new markers
  props.locations.forEach(location => {
    const icon = createCustomIcon()

    const marker = L.marker([location.latitude, location.longitude], {
      icon,
      title: location.name_location
    })
      .addTo(map)
      .on('click', (e) => {
        e.originalEvent.stopPropagation()
        emit('location-selected', location)
      })
      .bindPopup(`
        <div class="p-3 min-w-[180px]">
          <h3 class="font-bold text-neutral-900 dark:text-white text-sm mb-1">${location.name_location}</h3>
          <p class="text-xs text-neutral-600 dark:text-neutral-400 mb-2">${location.category.name_category}</p>
        </div>
      `)

    markers.value[location.id_location] = marker
  })

  // Fit bounds if there are locations
  if (props.locations.length > 0) {
    const bounds = L.latLngBounds(props.locations.map(l => [l.latitude, l.longitude]))
    if (bounds.isValid()) {
      map.fitBounds(bounds.pad(0.15))
    }
  }
}

const initMap = async () => {
  try {
    await nextTick()

    if (map) return

    if (!mapContainer.value) {
      setTimeout(initMap, 100)
      return
    }

    map = L.map(mapContainer.value, {
      zoomControl: false,
      preferCanvas: true,
      attributionControl: false,
      maxZoom: 19,
      minZoom: 3,
      maxBounds: [
        [-85, -180],
        [85, 180]
      ],
      maxBoundsViscosity: 0.5
    })

    // Hanya satu tile layer (OSM)
    tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxNativeZoom: 19,
      crossOrigin: true
    }).addTo(map)

    map.whenReady(() => {
      setTimeout(() => {
        map.invalidateSize();
        if (props.locations.length > 0) {
          const bounds = L.latLngBounds(props.locations.map(l => [l.latitude, l.longitude]))
          if (bounds.isValid()) {
            map.fitBounds(bounds.pad(0.1), { animate: false })
          }
        } else {
          map.setView([-8.65, 115.22], 13, { animate: false })
        }
      }, 200);
    });

    // Add zoom control
    if (!map.zoomControl) {
      L.control.zoom({
        position: 'bottomright',
        zoomInTitle: 'Perbesar',
        zoomOutTitle: 'Perkecil'
      }).addTo(map)
    }

    // Add attribution
    if (!map.attributionControl) {
      L.control.attribution({
        position: 'bottomleft',
        prefix: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map)
    }

    // Add markers
    addMarkers()

    mapInitialized.value = true
    emit('map-initialized', map)

    // Apply dark mode
    applyMapDarkMode()

  } catch (error) {
    console.error('Error initializing map:', error)
  }
}

const applyMapDarkMode = () => {
  if (!mapContainer.value) return

  const leafletPane = mapContainer.value.querySelector('.leaflet-pane')
  if (!leafletPane) return

  if (darkMode.value) {
    // Terapkan filter untuk dark mode
    leafletPane.style.filter = 'invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%)'

    // Pastikan marker tidak terkena filter
    const markers = mapContainer.value.querySelectorAll('.leaflet-marker-pane, .leaflet-shadow-pane')
    markers.forEach(marker => {
      marker.style.filter = 'invert(100%) hue-rotate(180deg)'
    })
  } else {
    // Hapus filter untuk light mode
    leafletPane.style.filter = ''

    const markers = mapContainer.value.querySelectorAll('.leaflet-marker-pane, .leaflet-shadow-pane')
    markers.forEach(marker => {
      marker.style.filter = ''
    })
  }

  // Invalidate size untuk refresh tampilan
  setTimeout(() => {
    if (map) map.invalidateSize()
  }, 50)
}

const selectLocationOnMap = (location) => {
  if (!map) return

  map.whenReady(() => {
    map.stop();
    try { map.invalidateSize() } catch (e) { }

    try {
      map.setView([location.latitude, location.longitude], 15, {
        animate: true,
        duration: 0.5
      })
    } catch (err) {
      console.warn('setView animation failed, fallback to panTo', err)
      try { map.panTo([location.latitude, location.longitude]) } catch (e) { }
    }

    // Open popup
    const marker = markers.value[location.id_location]
    if (marker) {
      try { marker.openPopup() } catch (e) { }
    }
  })
}

const clearSelectionOnMap = () => {
  if (!map) return

  // Close popup if open
  map.closePopup()

  // Return to default view
  if (props.locations.length > 0) {
    const bounds = L.latLngBounds(props.locations.map(l => [l.latitude, l.longitude]))
    if (bounds.isValid()) {
      map.fitBounds(bounds.pad(0.15))
    }
  }
}

const invalidateSize = () => {
  if (map) {
    map.invalidateSize(true)
  }
}

// Watchers
watch(() => props.locations, (newLocations) => {
  if (mapInitialized.value) {
    addMarkers()
  }
}, { deep: true })

watch(() => props.selectedLocation, (newLocation) => {
  if (newLocation && mapInitialized.value) {
    selectLocationOnMap(newLocation)
  } else if (!newLocation && mapInitialized.value) {
    clearSelectionOnMap()
  }
})

// Watch darkMode changes
watch(darkMode, () => {
  if (mapInitialized.value) {
    applyMapDarkMode()
  }
})

watch(() => props.sidebarOpen, () => {
  if (mapInitialized.value) {
    setTimeout(() => {
      invalidateSize()
    }, 300)
  }
})

// Lifecycle
onMounted(() => {
  initMap()
})

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
})

// Expose methods to parent
defineExpose({
  invalidateSize,
  selectLocationOnMap,
  clearSelectionOnMap
})
</script>

<style scoped>
.map-container {
  transition: margin-left 0.3s ease;
}

#map {
  z-index: 1;
}
</style>