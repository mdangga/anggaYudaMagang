<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const logoSrc = computed(() => {
    const path = usePage().props.profile_web?.logo_path
    return path ? `/storage/${path}` : ''
})
</script>

<template>
    <img :src="logoSrc" alt="logo">
</template>
