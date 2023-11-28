<script setup>
  import { onMounted } from 'vue';
  import { ref } from 'vue';
  import AgentListItem from './AgentListItem.vue';
  import { usePage } from '@inertiajs/vue3';
  import Agent from '@/Models/Agent'

  const page = usePage()
  const props = defineProps({
    agent: {
      type: Object,
      required: true
    }
  });

  var agent = new Agent()

  onMounted (async () => {
    // agent = new Agent(props.agent)
  });
</script>

<template>
  <div class="border border-gray-300 rounded-lg p-4 flex flex-row items-center justify-between">
    <div>
      <p class="block">{{ props.agent.name }}</p>
      <p class="text-xs text-gray-900 dark:text-white block">{{ props.agent.description }}</p>
    </div>
    <div class="inline-flex rounded-md shadow-sm" role="group">
      <a :href="'/availableagents/' + props.agent.id" type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blueGray bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
        <svg class="w-3 h-3 mr-3 text-blueGray dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 21 21">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.418 17.861 1 20l2.139-6.418m4.279 4.279 10.7-10.7a3.027 3.027 0 0 0-2.14-5.165c-.802 0-1.571.319-2.139.886l-10.7 10.7m4.279 4.279-4.279-4.279m2.139 2.14 7.844-7.844m-1.426-2.853 4.279 4.279"/>
        </svg>
        Edit
      </a>
      <button type="button" @click="agent.delete()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blueGray bg-white border-t border-b border-r rounded-r-lg border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
        <svg class="w-3 h-3 mr-3 text-blueGray dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z"/>
        </svg>
        Delete
      </button>
    </div>
  </div>
</template>
