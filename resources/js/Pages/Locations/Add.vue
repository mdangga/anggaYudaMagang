<script setup>
/* ================================
   IMPORT COMPONENTS
================================ */
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import LocationPicker from '@/Components/LocationPicker.vue'
import CompanyFormCard from '@/Components/CardPerusahaanForm.vue'
import ImageUpload from '@/Components/ImageUpload.vue'

/* ================================
   IMPORT UTILITIES
================================ */
import { useForm, usePage, Head } from '@inertiajs/vue3'
import { ref, onMounted, watch, computed } from 'vue'
import 'leaflet/dist/leaflet.css'

/* ================================
   PROPS & USER DATA
================================ */
const { auth } = usePage().props
const selectedFaculty = ref(null)
const { categories = [], faculties = [] } = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    faculties: {
        type: Array,
        default: () => []
    }
})

/* ================================
   DARK MODE HANDLER
================================ */
const darkMode = ref(false)

const applyDarkMode = () => {
    document.documentElement.classList.toggle('dark', darkMode.value)
}

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value
    localStorage.setItem('theme', darkMode.value ? 'dark' : 'light')
    applyDarkMode()
}

onMounted(() => {
    const savedTheme = localStorage.getItem('theme')
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)')

    if (savedTheme) {
        darkMode.value = savedTheme === 'dark'
    } else {
        darkMode.value = systemPrefersDark.matches

        if (systemPrefersDark.addEventListener) {
            systemPrefersDark.addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    darkMode.value = e.matches
                }
            })
        }
    }

    applyDarkMode()
})

const filteredDepartments = computed(() => {
    if (!selectedFaculty.value) return []

    const faculty = faculties.find(
        f => f.id_faculty === selectedFaculty.value
    )

    return faculty?.department || []
})

watch(darkMode, applyDarkMode)

watch(selectedFaculty, () => {
    form.id_department = null
})

/* ================================
   FORM
================================ */
const form = useForm({
    student_name: '',
    nim: '',
    name_location: '',
    description: '',
    contact: '',
    latitude: '',
    longitude: '',
    images: [],
    id_category: '',
    id_department: '',
    errors: {}
})

const submit = () => {
    form.post(route('locations.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => console.log('Form submission errors:', errors)
    })
}
</script>

<template>

    <Head title="Form Lokasi" />
    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-r from-primary to-primary-dark flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-white text-lg"></i>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Request Lokasi Magang</h1>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Tambahkan lokasi tempat Anda magang. Data akan diverifikasi terlebih dahulu.
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <form @submit.prevent="submit" class="space-y-6">

                <!-- Mahasiswa Information -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-graduate text-primary mr-2"></i>
                        Informasi Mahasiswa
                    </div>

                    <div class="card-content grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama -->
                        <div>
                            <InputLabel for="student_name" value="Nama Mahasiswa *" />
                            <TextInput id="student_name" type="text" class="form-input mt-1" v-model="form.student_name"
                                required placeholder="Nama lengkap mahasiswa" />
                            <InputError class="mt-2" :message="form.errors.student_name" />
                        </div>

                        <!-- NIM -->
                        <div>
                            <InputLabel for="nim" value="NIM *" />
                            <TextInput id="nim" type="text" class="form-input mt-1" v-model="form.nim" required
                                placeholder="Contoh: 123456789" />
                            <InputError class="mt-2" :message="form.errors.nim" />
                        </div>

                        <!-- Fakultas (UI only) -->
                        <div>
                            <InputLabel for="faculty" value="Fakultas *" />

                            <select id="faculty" class="form-select mt-1" v-model="selectedFaculty" required>
                                <option value="">Pilih Fakultas</option>
                                <option v-for="faculty in faculties" :key="faculty.id_faculty"
                                    :value="faculty.id_faculty">
                                    {{ faculty.name_faculty }}
                                </option>
                            </select>
                        </div>

                        <!-- Jurusan (yang dikirim ke backend) -->
                        <div>
                            <InputLabel for="id_department" value="Jurusan *" />

                            <select id="id_department" class="form-select mt-1" v-model="form.id_department"
                                :disabled="!selectedFaculty" required>
                                <option value="">Pilih Jurusan</option>
                                <option v-for="dept in filteredDepartments" :key="dept.id_department"
                                    :value="dept.id_department">
                                    {{ dept.name_department }}
                                </option>
                            </select>

                            <InputError class="mt-2" :message="form.errors.id_department" />
                        </div>

                    </div>
                </div>

                <!-- Company/Location -->
                <CompanyFormCard :categories="categories" :form="form" v-model="form.name_location"
                    v-model:description="form.description" v-model:id_category="form.id_category" />

                <!-- Coordinates -->
                <LocationPicker v-model:latitude="form.latitude" v-model:longitude="form.longitude"
                    :errorLatitude="form.errors.latitude" :errorLongitude="form.errors.longitude" />

                <!-- Image Upload -->
                <ImageUpload :form="form" />

                <!-- Actions -->
                <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

                        <div class="text-sm text-neutral-500 dark:text-neutral-400">
                            <span class="text-primary font-medium">*</span> Wajib diisi
                        </div>

                        <div class="flex items-center gap-4">
                            <Transition enter-active-class="transition-opacity duration-300"
                                leave-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
                                leave-to-class="opacity-0">
                                <p v-if="form.recentlySuccessful" class="text-sm text-success font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Permintaan berhasil dikirim!
                                </p>
                            </Transition>

                            <PrimaryButton :disabled="form.processing" class="btn-primary">
                                <i class="fas fa-paper-plane mr-2"></i>
                                {{ form.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</template>

<style scoped>
/* Card Styles */
.card {
    @apply bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700;
}

.card-header {
    @apply px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 text-lg font-semibold text-neutral-900 dark:text-white flex items-center;
}

.card-content {
    @apply p-6;
}

/* Form Inputs */
.form-input {
    @apply w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-transparent transition-colors;
}

.form-select {
    @apply w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-transparent transition-colors;
}

.form-textarea {
    @apply w-full px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-transparent transition-colors resize-none;
}

/* Buttons */
.btn-primary {
    @apply px-6 py-3 bg-primary hover:bg-primary-dark text-neutral-900 font-semibold rounded-lg transition-all duration-200 hover:shadow-md active:scale-95 flex items-center justify-center;
}

.btn-secondary {
    @apply px-4 py-2.5 border border-primary text-primary hover:bg-primary/10 dark:hover:bg-primary/20 font-medium rounded-lg transition-colors duration-200;
}

/* Success Text */
.text-success {
    @apply text-green-600 dark:text-green-400;
}

/* Responsive Adjustments */
@media (max-width: 640px) {
    .card-content {
        @apply p-4;
    }

    .card-header {
        @apply px-4 py-3;
    }
}
</style>
