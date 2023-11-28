<script setup>
  import { onMounted } from 'vue';
  import { ref } from 'vue';
  import WritingStyle from '@/Models/WritingStyle';
  import { useWritingStyleStore } from '@/Store/WritingStyle'
  import Button from '@/Components/UI/Button.vue';

  const loading = ref(true);
  const writingStyleStore = useWritingStyleStore()

  onMounted (async () => {
    await writingStyleStore.fetchAll()
  });
</script>

<template>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-6 py-3 uppercase">
              Schreibstile
            </th>
            <th scope="col" class="px-6 py-3 text-right">
              <Button :href="route('styles.create')" as="a" :style="'primary'" class="text-sm">
                <svg class="w-3 h-3 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 5.757v8.486M5.757 10h8.486M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Schreibstil hinzufügen
              </Button>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="writingStyle in writingStyleStore.writingStyles" :key="writingStyle.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ writingStyle.data.title }}
            </th>
            <td class="px-6 py-4 text-right">
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <a :href="'styles/' + writingStyle.data.id" type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blueGray bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
                  <svg class="w-3 h-3 mr-3 text-blueGray dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 21 21">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.418 17.861 1 20l2.139-6.418m4.279 4.279 10.7-10.7a3.027 3.027 0 0 0-2.14-5.165c-.802 0-1.571.319-2.139.886l-10.7 10.7m4.279 4.279-4.279-4.279m2.139 2.14 7.844-7.844m-1.426-2.853 4.279 4.279"/>
                  </svg>
                  Edit
                </a>
                <button type="button" @click="writingStylesStore.delete(writingStyle)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blueGray bg-white border-t border-b border-r rounded-r-lg border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
                  <svg class="w-3 h-3 mr-3 text-blueGray dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z"/>
                  </svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
</template>
