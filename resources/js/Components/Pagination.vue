<template>
    <nav class="mt-6 flex justify-end">
        <ul class="inline-flex items-center gap-1">
            <li v-for="(link, index) in links" :key="index">
                
                <!-- Previous & Next -->
                <a
                    v-if="isNav(link)"
                    :href="link.url || '#'"
                    @click="handleClick($event, link)"
                    class="inline-flex items-center justify-center min-w-[36px] h-9 rounded-md border text-sm font-medium transition"
                    :class="navClass(link)"
                >
                    {{ link.label.includes('Previous') ? '‹' : '›' }}
                </a>

                <!-- Page number -->
                <a
                    v-else-if="link.url"
                    :href="link.url"
                    @click="handleClick($event, link)"
                    v-html="link.label"
                    class="inline-flex items-center justify-center min-w-[36px] h-9 rounded-md border text-sm font-medium transition"
                    :class="pageClass(link)"
                />

                <!-- Ellipsis -->
                <span
                    v-else
                    class="inline-flex items-center justify-center min-w-[36px] h-9 text-sm text-gray-400"
                    v-html="link.label"
                />
            </li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: 'Pagination',

    props: {
        links: {
            type: Array,
            required: true
        }
    },

    methods: {
        isNav(link) {
            return link.label.includes('Previous') || link.label.includes('Next');
        },

        handleClick(event, link) {
            if (!link.url || link.active) {
                event.preventDefault();
                return;
            }

            this.$emit('page-changed', link);
        },

        navClass(link) {
            return link.url
                ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed';
        },

        pageClass(link) {
            return link.active
                ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
        }
    }
};
</script>
