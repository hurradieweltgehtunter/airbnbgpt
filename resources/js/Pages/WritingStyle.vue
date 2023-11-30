<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router } from '@inertiajs/vue3';
  import { computed, onMounted, ref, defineAsyncComponent } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import Spinner from '@/Components/Spinner.vue'
  import WritingStyle from '@/Models/WritingStyle'

  const page = usePage()
  const uploadComplete = ref(false)
  const loading = ref(false)
  const loadingMessage = ref('')

  const props = defineProps({
    section: {
      type: String,
      required: false
    },
    writingStyle: {
      type: Object,
      required: false,
      default: () => new WritingStyle()
    }
  })

  const writingStyle = ref(new WritingStyle(page.props.writingStyle))

  const currentComponent = computed({
    get: () => {
      switch (props.section) {
        case 'create':
          return defineAsyncComponent(() => import('@/Components/WritingStyle/Create.vue'))
        case 'show':
          return defineAsyncComponent(() => import('@/Components/WritingStyle/Show.vue'))
        default:
          return null
      }
    }
  })

  const setLoading = (value, message = null) => {
    loading.value = value
    if(message !== null) {
      loadingMessage.value = message
    }

    if(value === false) {
      loadingMessage.value = ''
    }
  }
</script>

<template>
  <Head title="Dashboard" />

  <BaseLayout>
    <transition name="fade" v-show="loading === true">
      <Spinner></Spinner>
    </transition>

    <transition v-show="loading === false">
      <component :is="currentComponent" :writingStyle="writingStyle" @loading="setLoading"></component>
    </transition>
  </BaseLayout>
</template>


<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 1s;
}
.fade-enter-from, .fade-leave-to {
  transition: opacity 1s;
  opacity: 0;
}

.textresult {
  initial-letter: 2;
}
</style>
