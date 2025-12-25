<script setup>
/* =========================
 * Import core
 * ========================= */
import { useForm } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';

/* =========================
 * Import component
 * ========================= */
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

/* =========================
 * Third-party
 * ========================= */
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

/* =========================
 * State, Props & Helper
 * ========================= */
const fileInput = ref(null);
const selectedFile = ref(null);

const props = defineProps({
    status: String,
    profile: Object,
});

const previewUrl = ref(
    props.profile.logo_path
        ? `/storage/${props.profile.logo_path}`
        : null
);

const notify = {
    success: (msg) => toast.success(msg, { autoClose: 5000 }),
    error: (msg) => toast.error(msg, { autoClose: 5000 })
}

const revokeBlob = () => {
    if (previewUrl.value?.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFiles = (event) => {
    const files = event.target.files || event.dataTransfer?.files;
    if (!files?.length) return;
    
    const file = files[0];
    
    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        form.setError('logo', 'File harus berupa JPG, PNG, atau GIF');
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        form.setError('logo', 'Ukuran file maksimal 2MB');
        return;
    }
    
    revokeBlob();
    
    selectedFile.value = file;
    form.logo = file;
    previewUrl.value = URL.createObjectURL(file);
    form.clearErrors('logo');
};

const removeFile = () => {
    revokeBlob();
    
    selectedFile.value = null;
    form.logo = null;
    previewUrl.value = props.profile.logo_path
    ? `/storage/${props.profile.logo_path}`
    : null;
    
    if (fileInput.value) fileInput.value.value = '';
    form.clearErrors('logo');
};

onUnmounted(revokeBlob);

const form = useForm({
    app_name: props.profile.app_name ?? '',
    description: props.profile.description ?? '',
    logo: null,
    _method: 'PATCH',
});

const submit = () => {
    form.post(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedFile.value = null;
            notify.success('Profil Berhasil Diperbarui')
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};
</script>

<template>
    <section>
        <header class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                Web Information Settings
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your website information and logo.
            </p>
        </header>

        <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">
            <!-- Application Name -->
            <div>
                <InputLabel for="app_name" value="Application Name" />
                <TextInput id="app_name" type="text" class="mt-2 block w-full" v-model="form.app_name" required
                    autofocus autocomplete="app_name" placeholder="Enter your application name" />
                <InputError class="mt-2" :message="form.errors.app_name" />
            </div>

            <!-- Description -->
            <div>
                <InputLabel for="description" value="Description" />
                <TextInput id="description" type="text" class="mt-2 block w-full" v-model="form.description" required
                    placeholder="Enter website description" />
                <InputError class="mt-2" :message="form.errors.description" />
            </div>

            <!-- Logo Upload Section -->
            <div>
                <InputLabel value="Website Logo" />
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-4">
                    Upload your website logo. Recommended size: 200×200px. Max size: 2MB.
                </p>

                <!-- Current Logo Preview -->
                <div v-if="previewUrl" class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 font-medium">Current Logo:</p>
                    <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <div class="relative">
                            <img :src="previewUrl" alt="Website logo"
                                class="w-24 h-24 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2"
                                @error="previewUrl = null" />
                            <button v-if="selectedFile" type="button" @click="removeFile"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors shadow"
                                aria-label="Remove selected logo">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <div class="flex-1">
                            <div v-if="selectedFile"
                                class="text-sm text-green-600 dark:text-green-400 p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                <i class="fas fa-check-circle mr-2"></i>
                                New logo selected
                            </div>
                            <div v-else-if="props.profile.logo_path"
                                class="space-y-2 text-sm text-gray-500 dark:text-gray-400">
                                <p class="flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                    Current logo will be replaced when you save
                                </p>
                                <p class="flex items-center">
                                    <i class="fas fa-sync-alt mr-2 text-blue-500"></i>
                                    If logo doesn't update, refresh with
                                    <kbd
                                        class="mx-1 px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow-sm">Ctrl</kbd>
                                    <span class="mx-1 text-gray-400">+</span>
                                    <kbd
                                        class="mx-1 px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow-sm">F5</kbd>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Area -->
                <div class="file-upload-area border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-6 cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-200 bg-white dark:bg-gray-900/50"
                    @click="triggerFileInput" @dragover.prevent="event => event.dataTransfer.dropEffect = 'copy'"
                    @drop.prevent="handleFiles" role="button" tabindex="0" @keydown.enter="triggerFileInput">
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 mb-4 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-500 dark:text-blue-400">
                            <i class="fas fa-cloud-upload-alt text-xl"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Click to upload or drag and drop
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            PNG, JPG, GIF up to 2MB
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                            Recommended: 200×200px
                        </p>
                    </div>
                    <input id="logo" ref="fileInput" type="file" class="hidden" accept="image/jpeg,image/png,image/gif"
                        @change="handleFiles" aria-label="Select logo file" />
                </div>

                <!-- Selected File Info -->
                <div v-if="selectedFile"
                    class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-image text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                    {{ selectedFile.name }}
                                </p>
                                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span>{{ (selectedFile.size / 1024).toFixed(1) }} KB</span>
                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                    <span>{{ selectedFile.type.replace('image/', '').toUpperCase() }}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" @click="removeFile"
                            class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            aria-label="Remove selected file">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <InputError v-if="form.errors && form.errors.logo" class="mt-3" :message="form.errors.logo" />
            </div>

            <!-- Form Actions -->
            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing" class="min-w-[120px]">
                        <span v-if="form.processing" class="flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Saving...
                        </span>
                        <span v-else class="flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </span>
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </section>
</template>

<style scoped>
.file-upload-area {
    min-height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.file-upload-area:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.file-upload-area:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}
</style>