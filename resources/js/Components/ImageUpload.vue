<script setup>
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    form: {
        type: Object,
        required: true
    }
})
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

                    <div class="file-upload-area mt-2" @click="$refs.fileInput.click()">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-neutral-400 mb-3"></i>

                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">
                                Klik untuk memilih gambar
                            </p>

                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                Format: JPG, PNG, GIF. Maksimal 2MB.
                            </p>
                        </div>

                        <input
                            id="image"
                            ref="fileInput"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="form.image = $event.target.files[0]"
                        />
                    </div>

                    <!-- File Name -->
                    <div v-if="form.image" class="mt-3">
                        <p class="text-sm text-neutral-700 dark:text-neutral-300">
                            File terpilih: {{ form.image.name }}
                        </p>
                    </div>

                    <InputError class="mt-2" :message="form.errors.image" />
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
}
</style>
