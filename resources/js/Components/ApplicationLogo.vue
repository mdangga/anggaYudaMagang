<script setup>
import { usePage } from '@inertiajs/vue3'
</script>

<template>
    <img :src="`/storage/${$page.props.profile_web.logo_path}`" alt="">
</template>
