<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';

const props = defineProps({
    status: String,
    profile: Object,
});

const fileInput = ref(null);
const selectedFile = ref(null);

const previewUrl = ref(
    props.profile.logo_path
        ? `/storage/${props.profile.logo_path}`
        : null
);

const form = useForm({
    app_name: props.profile.app_name ?? '',
    description: props.profile.description ?? '',
    logo: null,
    _method: 'PATCH',
});

// Cleanup blob URL
const revokeBlob = () => {
    if (previewUrl.value?.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
};

onUnmounted(revokeBlob);

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

const submit = () => {
    form.post(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedFile.value = null;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};
</script>


<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Web Information Settings
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your website information and logo.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6" enctype="multipart/form-data">
            <div>
                <InputLabel for="app_name" value="Application Name" />

                <TextInput
                    id="app_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.app_name"
                    required
                    autofocus
                    autocomplete="app_name"
                    placeholder="Enter your application name"
                />

                <InputError class="mt-2" :message="form.errors.app_name" />
            </div>

            <div>
                <InputLabel for="description" value="Description" />

                <TextInput
                    id="description"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.description"
                    required
                    placeholder="Enter website description"
                />

                <InputError class="mt-2" :message="form.errors.description" />
            </div>

            <div class="space-y-4">
                <div>
                    <InputLabel for="logo" value="Website Logo" />
                    
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-3">
                        Upload your website logo. Recommended size: 200×200px.
                    </p>

                    <!-- Current Logo Preview -->
                    <div v-if="previewUrl" class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Current Logo:</p>
                        <div class="flex items-start gap-4">
                            <div class="relative">
                                <img 
                                    :src="previewUrl" 
                                    alt="Website logo"
                                    class="w-32 h-32 object-contain rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-2"
                                    @error="previewUrl = null"
                                />
                                <button 
                                    v-if="selectedFile" 
                                    type="button" 
                                    @click="removeFile"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors"
                                    aria-label="Remove selected logo"
                                >
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                            <div class="flex-1 text-sm text-gray-500 dark:text-gray-400">
                                <p v-if="selectedFile" class="text-green-600 dark:text-green-400">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    New logo selected
                                </p>
                                <p v-else-if="props.profile.logo_path">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Current logo will be replaced when you save
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Area -->
                    <div 
                        class="file-upload-area border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition-colors group"
                        @click="triggerFileInput"
                        @dragover.prevent="event => event.dataTransfer.dropEffect = 'copy'"
                        @drop.prevent="handleFiles"
                        role="button"
                        tabindex="0"
                        @keydown.enter="triggerFileInput"
                    >
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-4 group-hover:text-blue-500 transition-colors"></i>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Click to upload or drag and drop
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                SVG, PNG, JPG or GIF (MAX. 2MB)
                            </p>
                        </div>

                        <input 
                            id="logo" 
                            ref="fileInput" 
                            type="file" 
                            class="hidden" 
                            accept="image/jpeg,image/png,image/gif,image/svg+xml"
                            @change="handleFiles"
                            aria-label="Select logo file"
                        />
                    </div>

                    <!-- Selected File Info -->
                    <div v-if="selectedFile" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded flex items-center justify-center">
                                    <i class="fas fa-file-image text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate max-w-xs">
                                        {{ selectedFile.name }}
                                    </p>
                                    <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ (selectedFile.size / 1024).toFixed(1) }} KB</span>
                                        <span>•</span>
                                        <span>{{ selectedFile.type.replace('image/', '').toUpperCase() }}</span>
                                    </div>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="removeFile"
                                class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                aria-label="Remove selected file"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <InputError v-if="form.errors && form.errors.logo" class="mt-2" :message="form.errors.logo" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <PrimaryButton :disabled="form.processing" class="min-w-[100px]">
                    <span v-if="form.processing" class="flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Saving...
                    </span>
                    <span v-else class="flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Save Changes
                    </span>
                </PrimaryButton>

                <Transition 
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-y-1"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Settings saved successfully!
                    </p>
                </Transition>

                <Transition 
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-y-1"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <p v-if="status" class="text-sm text-blue-600 dark:text-blue-400 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ status }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<style scoped>
.file-upload-area {
    min-height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.file-upload-area:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>