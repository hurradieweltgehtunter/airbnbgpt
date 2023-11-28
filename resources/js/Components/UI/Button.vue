<script setup>
  import { computed } from "vue";

  const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    style: {
      validator(value) {
        // The value must match one of these strings
        return ['primary', 'secondary', 'hollow', 'facebook'].includes(value)
      },
      default: 'primary',
      required: false
    },
    href: {
      type: String,
      default: null,
      required: false,
    },
    as: {
      type: String,
      default: 'button',
      required: false,
    //   validator(value) {
    //     // The value must match one of these strings
    //     return ['button', 'a'].includes(value)
    //   },
    },
  });

  const cssClasses = computed (() => {
    let classes = 'inline-flex items-center px-4 py-2 rounded-md font-semibold text-m shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150'

    switch(props.style) {
      case 'primary':
        classes += ' text-white bg-peachPink-500 hover:bg-peachPink-800'
        break;
      case 'secondary':
        classes += ' text-white bg-gray-200 text-gray-700 hover:bg-gray-300'
        break;
      case 'hollow':
        classes += ' bg-transparent text-gray-700 hover:bg-gray-100 border border-gray-400 hover:border-gray-500 hover:bg-gray-50 text-gray-500 hover:text-gray-700'
        break;
      case 'facebook':
        classes += ' text-white bg-facebook- hover:bg-facebook-hover'
        break;
    }

    return classes
  })
</script>

<template>
    <component :is="as"
      :href="href"
      :type="type"
      :class="cssClasses"
      >


        <slot />
    </component>
</template>
