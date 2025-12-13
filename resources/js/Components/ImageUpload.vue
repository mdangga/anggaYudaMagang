<script setup>
import { ref, watch, nextTick } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    form: {
        type: Object,
        required: true
    }
})

const fileInput = ref(null)
const localFiles = ref([]) // Menyimpan file secara lokal sebagai fallback

// Debug log untuk memantau perubahan
watch(() => props.form.images, (newVal) => {
    console.log('form.images changed:', newVal)
    console.log('Array?', Array.isArray(newVal))
    if (newVal) {
        console.log('Length:', newVal.length)
        newVal.forEach((file, i) => {
            console.log(`File ${i}:`, file.name, file.size, typeof file)
        })
    }
}, { deep: true })

const handleFiles = (event) => {
    const files = Array.from(event.target.files)
    console.log('Files selected:', files)

    if (files.length === 0) return

    // Cek apakah menggunakan Inertia.js form
    if (props.form && typeof props.form.set === 'function') {
        // Inertia.js form
        console.log('Using Inertia.js form.set')
        props.form.set('images', files)
    } else if (props.form && typeof props.form.images !== 'undefined') {
        // Vue reactive object
        console.log('Using Vue reactive object')
        props.form.images = files
    } else {
        // Fallback: simpan di localFiles
        console.log('Using localFiles fallback')
        localFiles.value = files
    }

    // Reset input untuk mengizinkan upload file dengan nama sama
    nextTick(() => {
        event.target.value = ''
    })
}

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click()
    }
}

const removeFile = (index) => {
    console.log('Removing file at index:', index)

    if (props.form && props.form.images && Array.isArray(props.form.images)) {
        // Hapus dari form.images
        props.form.images.splice(index, 1)

        // Jika menggunakan Inertia.js, perlu update form
        if (typeof props.form.set === 'function') {
            props.form.set('images', props.form.images)
        }
    } else if (localFiles.value.length > 0) {
        // Hapus dari localFiles
        localFiles.value.splice(index, 1)
    }

    // Reset file input
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

// Helper untuk mendapatkan daftar file yang akan ditampilkan
const getFilesToDisplay = () => {
    // Prioritas 1: files dari props.form.images
    if (props.form.images && Array.isArray(props.form.images) && props.form.images.length > 0) {
        console.log('Displaying from props.form.images')
        return props.form.images
    }

    // Prioritas 2: files dari localFiles
    if (localFiles.value.length > 0) {
        console.log('Displaying from localFiles')
        return localFiles.value
    }

    return []
}

// Tambahkan logging untuk melihat state saat komponen dimuat
console.log('Component mounted')
console.log('Initial form.images:', props.form.images)
console.log('Form object:', props.form)
</script>

<template>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-image text-primary mr-2"></i>
            Gambar Lokasi
        </div>

        <div class="card-content">
            <div class="space-y-4">
                <!-- Upload Area -->
                <div>
                    <InputLabel for="image" value="Upload Gambar" />

                    <div class="file-upload-area mt-2" @click="triggerFileInput" @dragover.prevent
                        @drop.prevent="handleFiles">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-neutral-400 mb-3"></i>

                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">
                                Klik atau tarik gambar ke sini
                            </p>

                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                Format: JPG, PNG, GIF. Maksimal 2MB.
                            </p>
                        </div>

                        <input id="image" ref="fileInput" type="file" multiple class="hidden"
                            accept="image/jpeg,image/png,image/gif" @change="handleFiles" />
                    </div>

                    <!-- Debug Info (Hapus di production) -->
                    <div v-if="false" class="mt-2 p-2 bg-yellow-100 text-xs text-gray-700 rounded">
                        <p>Debug Info:</p>
                        <p>form.images: {{ form.images }}</p>
                        <p>Type: {{ typeof form.images }}</p>
                        <p>Is Array: {{ Array.isArray(form.images) }}</p>
                        <p>Length: {{ form.images ? form.images.length : 0 }}</p>
                        <p>localFiles: {{ localFiles.length }}</p>
                    </div>

                    <!-- File List -->
                    <div v-if="getFilesToDisplay().length > 0" class="mt-4 space-y-2">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">
                            File terpilih: {{ getFilesToDisplay().length }} gambar
                        </p>

                        <div v-for="(file, index) in getFilesToDisplay()" :key="index"
                            class="flex items-center justify-between p-2 bg-neutral-100 dark:bg-neutral-700 rounded">
                            <div class="flex items-center space-x-2 flex-1 min-w-0">
                                <i class="fas fa-image text-neutral-500 flex-shrink-0"></i>
                                <span class="text-sm text-neutral-700 dark:text-neutral-300 truncate">
                                    {{ file.name }}
                                </span>
                                <span class="text-xs text-neutral-500 flex-shrink-0">
                                    ({{ (file.size / 1024).toFixed(1) }} KB)
                                </span>
                            </div>
                            <button type="button" @click="removeFile(index)"
                                class="text-red-500 hover:text-red-700 flex-shrink-0 ml-2" title="Hapus file">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="mt-4 text-center py-4 text-neutral-500 text-sm">
                        <i class="fas fa-folder-open mb-2"></i>
                        <p>Belum ada gambar yang dipilih</p>
                    </div>

                    <InputError v-if="form.errors && form.errors.images" class="mt-2" :message="form.errors.images" />
                    <InputError v-if="form.errors && form.errors['images.0']" class="mt-2"
                        :message="form.errors['images.0']" />
                </div>
            </div>
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

/* File Upload */
.file-upload-area {
    @apply border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg p-8 text-center cursor-pointer hover:border-primary dark:hover:border-primary transition-colors bg-neutral-50 dark:bg-neutral-900/50;
}

.file-upload-area:hover {
    @apply bg-primary/5;
}

/* Mobile */
@media (max-width: 640px) {
    .card-content {
        @apply p-4;
    }

    .card-header {
        @apply px-4 py-3;
    }

    .file-upload-area {
        @apply p-4;
    }
}
</style>