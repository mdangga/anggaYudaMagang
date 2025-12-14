<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { ref, provide, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const darkMode = ref(false)

provide('darkMode', darkMode)
provide('toggleDarkMode', (value) => {
    darkMode.value = value !== undefined ? value : !darkMode.value
    applyDarkMode()
    localStorage.setItem('theme', darkMode.value ? 'dark' : 'light')
})

const applyDarkMode = () => {
    document.documentElement.classList.toggle('dark', darkMode.value)
}

onMounted(() => {
    const saved = localStorage.getItem('theme')
    const mq = window.matchMedia('(prefers-color-scheme: dark)')

    if (saved) {
        darkMode.value = saved === 'dark'
    } else {
        darkMode.value = mq.matches
        // Listen for system preference changes
        if (mq.addEventListener) {
            mq.addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    darkMode.value = e.matches
                    applyDarkMode()
                }
            })
        }
    }
    applyDarkMode()
})

watch(darkMode, (newValue) => {
    applyDarkMode()
})
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800">
            <slot />
        </div>
    </div>
</template>
