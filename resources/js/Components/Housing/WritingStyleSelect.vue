<script setup>
  import { onMounted, ref, defineProps } from 'vue';
  import { Head, router } from '@inertiajs/vue3'
  import { usePage } from '@inertiajs/vue3'

  const page = usePage()
  const writingStyles = page.props.writingStyles
  const message = 'In welchem Schreibstil möchtest du deine Anzeige verfassen?'
  const selectedWritingStyle = ref(null)
  const emit = defineEmits(['loading', 'setProgress'])

  const props = defineProps({
    housing: {
      type: Object,
      required: true
    },
    loading: {
      type: Boolean,
      required: false,
      default: false
    },
    agent: {
      type: Object,
      required: false,
      default: () => null
    }
  })

  onMounted(async () => {

  })

  const send = async () => {
    emit('loading', true, 'Ich schreibe deine Anzeige…')

    router.get('/housings/' + props.housing.data.id + '/prepare/' + selectedWritingStyle.value)
  }
</script>

<template>
  <form @submit.prevent="send" class="w-full flex flex-col h-full justify-center items-center">
    <Head title="Schreibstil wählen" />
    <p class="w-2/3 font-normal text-gray-700 dark:text-gray-400 text-center mb-12">{{ message }}</p>
    <div class="w-2/3 font-serif text-lg mb-12">
      <select id="writingStyles" v-model="selectedWritingStyle" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="null">Schreibstil wählen...</option>
        <option v-for="writingStyle in writingStyles" :key="writingStyle.id" :value="writingStyle.id" >{{ writingStyle.title }}</option>

      </select>

    </div>

    <button type="submit" :disabled="selectedWritingStyle == null" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded mt-2">Text jetzt schreiben</button>
  </form>
</template>

<style scoped>
button[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

</style>
