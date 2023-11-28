<script setup>
  import { ref } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import { Head } from '@inertiajs/vue3';
  import Spinner from '@/Components/Spinner.vue'
  import AgentsList from '@/Components/Admin/AgentsList.vue';

  const page = usePage()

  const loading = ref(false)

</script>

<template>
  <div class="relative">

    <Head title="Agent anzeigen" />

    <!-- <Spinner v-if="loading" /> -->
    <AgentsList />

  </div>
</template>
