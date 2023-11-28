<template>
    <button
        class="text-white p-2 rounded flex items-center justify-center transition-colors duration-200 ease-in-out" :class="background"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>

<script setup>
import { computed } from "vue"

    const props = defineProps({
        style: {
            type: String,
            required: false,
            default: ''
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    })

    const background = computed(() => {
        switch (props.style) {
            case 'facebook':
                return 'bg-facebook- hover:bg-facebook-hover'
            default:
                return 'bg-peachPink-500 hover:bg-peachPink-800'
        }
    })
</script>

<style scoped>
button[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}
</style>
