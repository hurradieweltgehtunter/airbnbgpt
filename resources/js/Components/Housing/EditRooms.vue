<script setup>
  import { Head, router } from '@inertiajs/vue3';
  import { computed, onMounted, ref } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import axios from 'axios'

  import Spinner from '@/Components/Spinner.vue'
  import Housing from '@/Models/Housing'
  import HousingRoom from '@/Models/HousingRoom'
  import HousingImage from '@/Models/HousingImage'
  import Agent from '@/Models/Agent'

  import { useHousingStore } from '@/Store/housing'

  const housingStore = useHousingStore()

  const props = defineProps({
    housing: {
      type: Object,
      required: true
    }
  })

  const loading = ref(false)

  const finish = async () => {

  }
</script>

<template>
  <div>
    <div v-for="room in housing.rooms" :key="room.data.id" class="mb-12">
      <label :for="'description-' + room.data.id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ room.data.name }}</label>
      <textarea :for="'description-' + room.data.id" v-model="room.data.description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your thoughts here..."></textarea>
    </div>
    <button @click="finish()" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded mt-2">Texte ok, let's go</button>
  </div>
</template>


