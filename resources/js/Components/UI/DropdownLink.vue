<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        required: false,
        default: false,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white transition duration-150 ease-in-out"
        :class="{ 'bg-gray-100': active }"
    >
        <slot />
    </Link>
</template>

<style  >
    .active {
        text-decoration: underline;
    }
</style>
