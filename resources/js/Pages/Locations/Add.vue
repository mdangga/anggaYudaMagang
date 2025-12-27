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
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css'

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

    return faculty?.departments || []
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
        onSuccess: () => {
            toast.success("Permintaan berhasil dikirim !", {
                position: "top-right",
                autoClose: 5000,
            });
            form.reset()
        },
        onError: (errors) => {
            toast.error("Error! Data gagal validasi", {
                position: "top-right",
                autoClose: 5000,
            });
            console.error('Form submission errors:', errors)
        }
    })
}
</script>

<template>
    <Head :title="`Form Lokasi - ${$page.props.profile_web.app_name}`" />
    
    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-r from-primary to-primary-dark flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white text-lg" viewBox="0 0 24 24">
                            <path fill="currentColor" fill-rule="evenodd"
                                d="M11.291 21.706L12 21zM12 21l.708.706a1 1 0 0 1-1.417 0l-.006-.007l-.017-.017l-.062-.063a48 48 0 0 1-1.04-1.106a50 50 0 0 1-2.456-2.908c-.892-1.15-1.804-2.45-2.497-3.734C4.535 12.612 4 11.248 4 10c0-4.539 3.592-8 8-8s8 3.461 8 8c0 1.248-.535 2.612-1.213 3.87c-.693 1.286-1.604 2.585-2.497 3.735a50 50 0 0 1-3.496 4.014l-.062.063l-.017.017l-.006.006zm0-8a3 3 0 1 0 0-6a3 3 0 0 0 0 6"
                                clip-rule="evenodd" />
                        </svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary mr-2" viewBox="0 0 15 15">
                            <path fill="currentColor"
                                d="M7.5 1L0 4.5l2 .9v1.7c-.6.2-1 .8-1 1.4s.4 1.2 1 1.4v.1l-.9 2.1C.8 13 1 14 2.5 14s1.7-1 1.4-1.9L3 10c.6-.3 1-.8 1-1.5s-.4-1.2-1-1.4V5.9L7.5 8L15 4.5zm4.4 6.5l-4.5 2L5 8.4v.1c0 .7-.3 1.3-.8 1.8l.6 1.4v.1c.1.4.2.8.1 1.2c.7.3 1.5.5 2.5.5c3.3 0 4.5-2 4.5-3z" />
                        </svg>
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
                            <!-- <Transition enter-active-class="transition-opacity duration-300"
                                leave-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
                                leave-to-class="opacity-0">
                                <p v-if="form.recentlySuccessful" class="flex text-sm text-success font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" viewBox="0 0 24 24">
                                        <path fill="currentColor" fill-rule="evenodd"
                                            d="M12 21a9 9 0 1 0 0-18a9 9 0 0 0 0 18m-.232-5.36l5-6l-1.536-1.28l-4.3 5.159l-2.225-2.226l-1.414 1.414l3 3l.774.774z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Permintaan berhasil dikirim!
                                </p>
                            </Transition> -->

                            <PrimaryButton :disabled="form.processing" class="btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M20.04 2.323c1.016-.355 1.992.621 1.637 1.637l-5.925 16.93c-.385 1.098-1.915 1.16-2.387.097l-2.859-6.432l4.024-4.025a.75.75 0 0 0-1.06-1.06l-4.025 4.024l-6.432-2.859c-1.063-.473-1-2.002.097-2.387z" />
                                </svg>
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
