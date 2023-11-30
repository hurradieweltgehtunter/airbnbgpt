<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router } from '@inertiajs/vue3';
  import { onMounted, ref, defineEmits, computed } from 'vue';
  import WritingStyleExample from '@/Models/WritingStyleExample'
  import WritingStyle from '@/Models/WritingStyle'
  import Divider from '@/Components/UI/Divider.vue'
  import Button from '@/Components/UI/Button.vue'

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

  const isSaveable = computed (() => {
    return props.writingStyle.data.title.length < 1 || props.writingStyle.data.description.length < 1
  })

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
    // emit('loading', true)
    let response = await axios.post(route('styles.analyzeExample'), { content: writingStyleExample.data.content})
    let ws = new WritingStyle(response.data)

    // Merge props.writingStyle.data and ws.data
    props.writingStyle.data = {...props.writingStyle.data, ...ws.data}
    emit('loading', false)
  }

</script>

<template>
  <div>
    <h1>Neuen Schreibstil anlegen</h1>
    <Head title="Neuen Schreibstil anlegen" />

    <div class="mb-6">
      <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Titel</label>
      <input type="text" id="title" v-model="props.writingStyle.data.title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    </div>

    <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Wie soll AirBnB-GPT deine Anzeige verfassen? Beschreibe den gewünschten Schreibstil.</label>
    <textarea id="description" v-model="props.writingStyle.data.description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder=""></textarea>

    <div class="flex justify-end items-center mt-2">
      <Button variant="primary" :disabled="isSaveable" @click="saveStyle()">Speichern</Button>
    </div>

    <divider />

    <h2 class="text-2xl font-extrabold dark:text-white mb-4">Schreibstil anhand von Beispieltexten analysieren</h2>
    <p>Gib hier Texte an, deren Stil du für deine AirBnB-Inserate kopieren möchtest. Um eine wertige Analyse erstellen zu können muss der zu analysierende Text mindestens 500 Zeichen lang sein.</p>
    <textarea id="example" v-model="writingStyleExample.data.content" rows="10" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder=""></textarea>
    <div class="flex justify-end items-center mt-2 text-xs">
      {{ writingStyleExample.data.content.length }} / 500 Zeichen
    </div>
    <div class="flex justify-end items-center mt-2">
      <Button :disabled="writingStyleExample.data.content.length <= 500" variant="primary" @click="runWritingStyleAnalyzer()">Text analysieren</Button>
    </div>
  </div>
</template>
