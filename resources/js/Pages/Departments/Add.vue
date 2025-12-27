<script setup>
/* ================================
   IMPORT COMPONENTS
================================ */
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

/* ================================
   IMPORT UTILITIES
================================ */
import { useForm, usePage, Head } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'

/* ================================
   PROPS & USER DATA
================================ */
const { faculties = [] } = defineProps({
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

watch(darkMode, applyDarkMode)

/* ================================
   FORM
================================ */
const form = useForm({
    name_department: '',
    degree_level: '',
    id_faculty: '',
    errors: {}
})

const submit = () => {
    form.post(route('department.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => console.error('Form submission errors:', errors)
    })
}
</script>

<template>
    <Head :title="`Form Jurusan - ${$page.props.profile_web.app_name}`" />

    <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-primary dark:bg-primary-dark flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white dark:text-neutral-900"
                            viewBox="0 0 32 32">
                            <g fill="currentColor">
                                <path
                                    d="M28.99 30V16.98c0-.54-.44-.99-.99-.99h-8V12h2v1.19c0 .45.36.81.8.81h3.39c.45 0 .81-.36.81-.8V4.81c0-.45-.36-.81-.8-.81h-3.39c-.45 0-.81.36-.81.81V6h-2V2.92c0-.51-.41-.92-.92-.92H3.92c-.51 0-.92.41-.92.92v27.06h1.98v-4.95c0-.57.46-1.03 1.03-1.03h10.96c.57 0 1.03.46 1.03 1.03V30h-.99v-4.65c0-.19-.15-.35-.35-.35h-2.28c-.19 0-.35.15-.35.35V30H11zM20 27.01V24h1.5c.28 0 .5.23.5.5v2c0 .28-.22.5-.5.51zm0-5V19h1.5c.28 0 .5.23.5.5v2c0 .28-.22.5-.5.51zM22 11h-2V7h2zm5 8.5v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0 5v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V24.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m-13.99-20v2c0 .28-.22.5-.5.5h-2.02c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0 7c0 .28-.22.5-.5.5h-2.02c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0 3v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V14.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0 5v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m5.01 2c0 .28-.23.5-.5.51H15.5c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.23.5-.5.51H15.5c-.28 0-.5-.23-.5-.5V14.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0-3c0 .28-.23.5-.5.5H15.5c-.28 0-.5-.23-.5-.5v-2c0-.28.22-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.23.5-.5.5H15.5c-.28 0-.5-.22-.5-.5v-2c0-.28.22-.5.5-.5h2.02c.28 0 .5.23.5.5m-10.01 17c0 .28-.22.5-.5.51H5.49c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.22.5-.5.51H5.49c-.28 0-.5-.23-.5-.5V14.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0-3c0 .28-.22.5-.5.5H5.49c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.22.5-.5.5H5.49c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5" />
                                <path
                                    d="M13 30h-2.98v-4.65c0-.2.16-.35.35-.35h2.28c.19 0 .35.15.35.35zm-4.02-4.65V30H6v-4.65c0-.2.15-.35.35-.35h2.28c.2 0 .35.16.35.35" />
                            </g>
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Tambah Jurusan</h1>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Tambahkan jurusan baru ke dalam sistem.
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <form @submit.prevent="submit" class="space-y-6">

                <!-- Department Information -->
                <div class="card">
                    <div class="card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary mr-2" viewBox="0 0 32 32">
                            <g fill="currentColor">
                                <path
                                    d="M28.99 30V16.98c0-.54-.44-.99-.99-.99h-8V12h2v1.19c0 .45.36.81.8.81h3.39c.45 0 .81-.36.81-.8V4.81c0-.45-.36-.81-.8-.81h-3.39c-.45 0-.81.36-.81.81V6h-2V2.92c0-.51-.41-.92-.92-.92H3.92c-.51 0-.92.41-.92.92v27.06h1.98v-4.95c0-.57.46-1.03 1.03-1.03h10.96c.57 0 1.03.46 1.03 1.03V30h-.99v-4.65c0-.19-.15-.35-.35-.35h-2.28c-.19 0-.35.15-.35.35V30H11zM20 27.01V24h1.5c.28 0 .5.23.5.5v2c0 .28-.22.5-.5.51zm0-5V19h1.5c.28 0 .5.23.5.5v2c0 .28-.22.5-.5.51zM22 11h-2V7h2zm5 8.5v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0 5v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V24.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m-13.99-20v2c0 .28-.22.5-.5.5h-2.02c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0 7c0 .28-.22.5-.5.5h-2.02c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0 3v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V14.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0 5v2c0 .28-.22.5-.5.51h-2.02c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m5.01 2c0 .28-.23.5-.5.51H15.5c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.23.5-.5.51H15.5c-.28 0-.5-.23-.5-.5V14.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0-3c0 .28-.23.5-.5.5H15.5c-.28 0-.5-.23-.5-.5v-2c0-.28.22-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.23.5-.5.5H15.5c-.28 0-.5-.22-.5-.5v-2c0-.28.22-.5.5-.5h2.02c.28 0 .5.23.5.5m-10.01 17c0 .28-.22.5-.5.51H5.49c-.28 0-.5-.23-.5-.5V19.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.22.5-.5.51H5.49c-.28 0-.5-.23-.5-.5V14.5c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5m0-3c0 .28-.22.5-.5.5H5.49c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5zm0-7v2c0 .28-.22.5-.5.5H5.49c-.28 0-.5-.23-.5-.5v-2c0-.28.23-.5.5-.5h2.02c.28 0 .5.23.5.5" />
                                <path
                                    d="M13 30h-2.98v-4.65c0-.2.16-.35.35-.35h2.28c.19 0 .35.15.35.35zm-4.02-4.65V30H6v-4.65c0-.2.15-.35.35-.35h2.28c.2 0 .35.16.35.35" />
                            </g>
                        </svg>
                        Informasi Jurusan
                    </div>

                    <div class="card-content grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Degree Level -->
                        <div>
                            <InputLabel for="degree_level" value="Gelar *" />
                            <TextInput id="degree_level" type="text" class="form-input mt-1" v-model="form.degree_level"
                                required placeholder="Gelar (S1,S2,S3,dll)" />
                            <InputError class="mt-2" :message="form.errors.degree_level" />
                        </div>

                        <!-- Nama -->
                        <div>
                            <InputLabel for="name_department" value="Nama Jurusan *" />
                            <TextInput id="name_department" type="text" class="form-input mt-1"
                                v-model="form.name_department" required
                                placeholder="Hukum Hindu, Teknik Informatika, dll" />
                            <InputError class="mt-2" :message="form.errors.name_department" />
                        </div>
                        <div>
                            <InputLabel for="id_faculty" value="Fakultas *" />

                            <select id="id_faculty" class="form-select mt-1" v-model="form.id_faculty" required>
                                <option value="">Pilih Fakultas</option>
                                <option v-for="faculty in faculties" :key="faculty.id_faculty"
                                    :value="faculty.id_faculty">
                                    {{ faculty.name_faculty }}
                                </option>
                            </select>

                            <InputError class="mt-2" :message="form.errors.id_faculty" />
                        </div>
                    </div>
                </div>

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
                                <p v-if="form.recentlySuccessful" class="flex text-sm text-success font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" viewBox="0 0 24 24">
                                        <path fill="currentColor" fill-rule="evenodd"
                                            d="M12 21a9 9 0 1 0 0-18a9 9 0 0 0 0 18m-.232-5.36l5-6l-1.536-1.28l-4.3 5.159l-2.225-2.226l-1.414 1.414l3 3l.774.774z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Permintaan berhasil dikirim!
                                </p>
                            </Transition>

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
