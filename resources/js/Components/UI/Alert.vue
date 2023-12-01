<script setup>
  import { computed } from "vue";

  const props = defineProps({
    variant: {
      validator(value) {
        // The value must match one of these strings
        return ['info', 'warning', 'error', 'success'].includes(value)
      },
      type: String,
      default: 'info',
      required: false
    },
  });

  const cssClasses = computed (() => {
    let classes = 'p-4 mb-4 text-sm rounded-lg flex items-center'

    switch(props.variant) {
      case 'info':
        classes += ' text-blue-800 bg-blue-50 dark:bg-gray-800 dark:text-blue-400'
        break;
      case 'success':
        classes += ' text-green-800 bg-green-50 dark:bg-gray-800 dark:text-green-400'
        break;
      case 'warning':
        classes += ' text-yellow-800 bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300'
        break;
      case 'danger':
        classes += ' text-red-800 bg-red-50 dark:bg-gray-800 dark:text-red-400'
        break;
    }

    return classes
  })
</script>

<template>
  <div :class="cssClasses" role="alert">
    <svg v-if="props.variant === 'warning'" class="w-4 h-4 mr-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
    </svg>

    <svg v-else class="w-4 h-4 mr-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h2v5m-2 0h4M9.408 5.5h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
    </svg>

    <slot></slot>
  </div>
</template>
