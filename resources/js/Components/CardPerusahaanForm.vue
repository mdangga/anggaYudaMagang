<script setup>
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
    form: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    }
})
</script>

<template>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-building text-primary mr-2"></i>
            Informasi Perusahaan/Lokasi
        </div>

        <div class="card-content space-y-6">

            <!-- Name Location -->
            <div>
                <InputLabel for="name_location" value="Nama Perusahaan/Lokasi *" />

                <TextInput id="name_location" type="text" class="form-input mt-1" v-model="form.name_location" required
                    placeholder="Contoh: PT. Contoh Teknologi" />

                <InputError class="mt-2" :message="form.errors.name_location" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category -->
                <div>
                    <InputLabel for="id_category" value="Kategori *" />
    
                    <select id="id_category" class="form-select mt-1" v-model="form.id_category" required>
                        <option value="">Pilih Kategori</option>
                        <option v-for="category in categories" :key="category.id_category" :value="category.id_category">
                            {{ category.name_category }}
                        </option>
                    </select>
    
                    <InputError class="mt-2" :message="form.errors.id_category" />
                </div>
    
                <!-- Contact -->
                <div>
                    <InputLabel for="contact" value="Contact Person *" />
    
                    <TextInput id="contact" type="text" class="form-input mt-1" v-model="form.contact" required
                        inputmode="numeric" pattern="^[0-9+]{9,15}$" placeholder="Contoh: 081234567890 / +6281234567890" />
    
                    <InputError class="mt-2" :message="form.errors.contact" />
                </div>
            </div>

            <!-- Description -->
            <div>
                <InputLabel for="description" value="Deskripsi *" />

                <textarea id="description" class="form-textarea mt-1" v-model="form.description" rows="4" required
                    placeholder="Deskripsikan perusahaan, program magang yang ditawarkan, dll."></textarea>

                <InputError class="mt-2" :message="form.errors.description" />
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
