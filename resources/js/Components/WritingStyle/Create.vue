<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router } from '@inertiajs/vue3';
  import { onMounted, ref, defineEmits } from 'vue';
  import WritingStyleExample from '@/Models/WritingStyleExample'
  import WritingStyle from '@/Models/WritingStyle'

  const props = defineProps({
    section: {
      type: String,
      required: false
    },
    writingStyle: {
      type: Object,
      required: true,
    }
  })

  const emit = defineEmits(['loading'])

  const writingStyleExample = new WritingStyleExample()
  const writingStyle = new WritingStyle()
  const writingStyleDescription = ref('')
  const exampleText = ref('')

  onMounted (async () => {
    writingStyle.setData(props.writingStyle.data)
  });

  const saveStyle = async () => {
    emit('loading', true)
    await props.writingStyle.save()
    emit('loading', false)
    router.get('/dashboard')
  }

  const saveExample = async () => {
    emit('loading', true)
    await writingStyle.create()

    let writingStyleExample = writingStyle.createExample(exampleText.value)

    await runWritingStyleAnalyzer()

  }

  const runWritingStyleAnalyzer = async () => {
    let agent = await writingStyle.createAgent()

    writingStyle.setData(await agent.run())

    router.get('/styles/' + writingStyle.data.id)
  }

</script>

<template>
  <div>
    <h1>New Writing Style</h1>

    <div class="mb-6">
      <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Titel</label>
      <input type="text" id="title" v-model="props.writingStyle.data.title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    </div>

    <label for="example" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Wie soll AirBnB-GPT deine Anzeige verfassen? Beschreibe den gewünschten Schreibstil.</label>
    <textarea id="example" v-model="props.writingStyle.data.description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder=""></textarea>

    <div class="flex justify-end items-center mt-2">
      <button @click="saveStyle()" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded mt-2">Speichern und analysieren</button>
    </div>
  </div>
</template>
