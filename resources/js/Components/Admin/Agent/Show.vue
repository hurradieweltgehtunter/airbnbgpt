<script setup>
  import { ref } from 'vue';
  import { usePage, router, Head } from '@inertiajs/vue3'
  import Spinner from '@/Components/Spinner.vue'
  import Agent from '@/Models/Agent'

  const page = usePage()
  const agent = new Agent(page.props.agent)
  const loading = ref(false)
  const save = async () => {
    loading.value = true

    await axios.put(route('availableagents.update', page.props.agent.data.id), page.props.agent.data)
    router.get(route('availableagents.index'))
  }
</script>

<template>
  <div class="relative">
    <Head title="Agent anzeigen" />

    <Spinner v-if="loading" />

    <h1>{{ page.props.agent.data.name }}</h1>
    {{ page.props.agent.data.description }}

    <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">System Prompt</label>
    <textarea id="message" v-model="page.props.agent.data.system_prompt" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your thoughts here..."></textarea>

    <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Initial message</label>
    <textarea id="message" oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"' v-model="page.props.agent.data.initial_message.content" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your thoughts here..."></textarea>

    <label class="relative inline-flex items-center cursor-pointer">
      <input type="checkbox" v-model="page.props.agent.data.fake_enabled" class="sr-only peer">
      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
      <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Fake enabled</span>
    </label>

    <div class="flex justify-end items-center mt-2">
      <button @click="save()" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded">Speichern</button>
    </div>

  </div>
</template>


