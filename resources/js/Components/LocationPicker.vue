<script setup>
import { ref, onMounted, nextTick, watch, defineProps, defineEmits, computed } from "vue"
import { usePage } from "@inertiajs/vue3"
import InputLabel from "@/Components/InputLabel.vue"
import InputError from "@/Components/InputError.vue"
import TextInput from "@/Components/TextInput.vue"
import L from "leaflet"
import "leaflet/dist/leaflet.css"

// Props
const props = defineProps({
    latitude: { type: [String, Number], default: "" },
    longitude: { type: [String, Number], default: "" },
    errorLatitude: { type: String, default: "" },
    errorLongitude: { type: String, default: "" }
})

// Emits
const emit = defineEmits(["update:latitude", "update:longitude"])

// Dark mode detection
const darkMode = computed(() => {
    return document.documentElement.classList.contains('dark')
})

// Helpers
const updateLat = (val) => emit("update:latitude", Number(val).toFixed(6))
const updateLng = (val) => emit("update:longitude", Number(val).toFixed(6))

// Refs
const mapEl = ref(null)
let map = null
let marker = null
let tiles = null

// Map Initialization
const initMap = () => {
    if (map || !mapEl.value) return

    const lat = props.latitude ? parseFloat(props.latitude) : -8.65
    const lng = props.longitude ? parseFloat(props.longitude) : 115.2167

    map = L.map(mapEl.value, {
        inertia: true,
        zoomControl: false,
        attributionControl: false,
    }).setView([lat, lng], 11)

    tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxNativeZoom: 19,
        crossOrigin: true
    }).addTo(map)

    // Apply dark mode filter if needed
    if (darkMode.value) {
        const mapContainer = mapEl.value
        if (mapContainer) {
            const leafletPane = mapContainer.querySelector('.leaflet-pane')
            if (leafletPane) {
                leafletPane.style.filter = 'invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%)'
            }
        }
    }

    L.control.zoom({ position: "topright" }).addTo(map)
    L.control.attribution({ position: "bottomleft" }).addTo(map)

    if (props.latitude && props.longitude) {
        createMarker(parseFloat(props.latitude), parseFloat(props.longitude))
        map.setView([props.latitude, props.longitude], 15)
    }

    // Fix map after mount
    setTimeout(() => map.invalidateSize(), 150)

    map.on("click", ({ latlng }) => {
        updateLat(latlng.lat)
        updateLng(latlng.lng)
        createMarker(latlng.lat, latlng.lng)
    })
}

const createCustomIcon = (isSelected = false) => {
    return L.divIcon({
        html: `
            <div class="relative transition-transform duration-200">
                <div class="flex items-center justify-center transform transition-all duration-300 hover:scale-110">
                    <i class="fas fa-map-marker-alt text-primary text-3xl"></i>
                </div>
            </div>
        `,
        className: 'custom-marker',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    })
}

const createMarker = (lat, lng) => {
    if (marker) {
        marker.setLatLng([lat, lng])
    } else {
        marker = L.marker([lat, lng], {
            icon: createCustomIcon(true),
            draggable: true
        }).addTo(map)
        
        marker.on('dragend', (event) => {
            const position = event.target.getLatLng()
            updateLat(position.lat)
            updateLng(position.lng)
        })
    }
}

// Lifecycle
onMounted(() => {
    nextTick(() => {
        if (mapEl.value) {
            initMap()
        }
    })
})

// Watch parent changes
watch(() => [props.latitude, props.longitude], ([lat, lng]) => {
    if (!map || !lat || !lng) return
    createMarker(parseFloat(lat), parseFloat(lng))
    map.setView([lat, lng], map.getZoom())
}, { immediate: true })

// Watch dark mode changes
watch(darkMode, (newVal) => {
    if (!map || !mapEl.value) return
    
    const mapContainer = mapEl.value
    const leafletPane = mapContainer.querySelector('.leaflet-pane')
    
    if (leafletPane) {
        if (newVal) {
            leafletPane.style.filter = 'invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%)'
        } else {
            leafletPane.style.filter = ''
        }
        map.invalidateSize()
    }
})

// Device Location
const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        alert("Geolocation tidak didukung browser.")
        return
    }

    navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
            updateLat(coords.latitude)
            updateLng(coords.longitude)
        },
        (err) => alert("Gagal mendapatkan lokasi: " + err.message),
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
    )
}
</script>

<template>
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 text-lg font-semibold text-neutral-900 dark:text-white flex items-center">
            <i class="fas fa-map-pin text-primary mr-2"></i>
            Koordinat Lokasi
        </div>

        <div class="p-6 space-y-6">
            <!-- Input Lat / Lng -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="latitude" value="Latitude *" class="" />
                    <TextInput id="latitude" type="number" step="any" class="form-input" :modelValue="props.latitude"
                        @update:modelValue="updateLat" required placeholder="-6.2088" />
                    <InputError class="mt-2" :message="props.errorLatitude" />
                </div>

                <div>
                    <InputLabel for="longitude" value="Longitude *" />
                    <TextInput id="longitude" type="number" step="any" class="form-input" :modelValue="props.longitude"
                        @update:modelValue="updateLng" required placeholder="106.8456" />
                    <InputError class="mt-2" :message="props.errorLongitude" />
                </div>
            </div>

            <!-- Button -->
            <div class="space-y-3">
                <button type="button" @click="getCurrentLocation"
                    class="px-4 py-2.5 border border-primary text-primary hover:bg-primary/10 dark:hover:bg-primary/20 font-medium rounded-lg transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-location-dot mr-2"></i>
                    Dapatkan Lokasi Saat Ini
                </button>

                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Atau klik peta di bawah untuk memilih lokasi
                </p>
            </div>

            <!-- Map -->
            <div class="space-y-2">
                <InputLabel value="Pilih Lokasi di Peta" />
                <div ref="mapEl"
                    class="w-full h-64 rounded-lg border border-neutral-300 dark:border-neutral-600 overflow-hidden shadow-sm">
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    Klik peta untuk menempatkan pin. Geser pin untuk penyesuaian.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-input {
    @apply w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-transparent transition-colors;
}
</style>