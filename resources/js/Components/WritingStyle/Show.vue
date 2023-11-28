<script setup>
  import { ref } from 'vue';
  import { usePage, router } from '@inertiajs/vue3'
  const page = usePage()

  const props = defineProps({
    writingStyle: {
      type: Object,
      required: true
    }
  })

  const loading = ref(false)

  const save = async () => {
    props.writingStyle.save()
    router.get('/dashboard')
  }

</script>

<template>
  <div>
    <div class="mb-6">
      <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Titel</label>
      <input type="text" id="title" v-model="props.writingStyle.data.title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    </div>

    <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Stilanweisung</label>
    <textarea id="description" v-model="props.writingStyle.data.description" rows="6" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>

    <div class="flex justify-end items-center mt-2">
      <button @click="save" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded">Speichern</button>
    </div>
  </div>
</template>
