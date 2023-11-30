<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head } from '@inertiajs/vue3';
  import { computed, onMounted, ref } from 'vue';
  import HousingsList from '@/Components/Housing/HousingsList.vue';
  import WritingStylesList from '@/Components/WritingStyle/WritingStylesList.vue';
  import Spinner from '@/Components/Spinner.vue';
  import { useHousingStore } from '@/Store/Housing'
  import { useWritingStyleStore } from '@/Store/WritingStyle'

  const housingStore = useHousingStore()
  const writingStyleStore = useWritingStyleStore()

  onMounted (async () => {
    // Fill housing store with props.housings.data
    housingStore.fill(props.housings.data)
    writingStyleStore.fill(props.writingStyles.data)
  });

  const props = defineProps({
    housings: {
      type: Object,
      required: true
    },
    writingStyles: {
      type: Object,
      required: true
    }
  })

  const showSpinner = computed(() => {
    // if housingStore.isLoading is true, show the loader, if all are false, hide it
    return housingStore.isLoading && useWritingStyleStore.isLoading
  })

</script>

<template>
  <Head title="Dashboard" />

  <BaseLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </template>

    <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-6xl dark:text-white">Dashboard</h1>
    <Spinner v-if="showSpinner" />
    <HousingsList :housings="housings.data" @setLoading="handleLoading('housings')" />

    <hr class="h-px my-6 bg-gray-200 border-0 dark:bg-gray-700">

    <WritingStylesList @setLoading="handleLoading('WritingStyles')" />

  </BaseLayout>
</template>
