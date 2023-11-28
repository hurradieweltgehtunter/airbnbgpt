<script setup>
  import { ref, computed, onMounted } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import { Head } from '@inertiajs/vue3';
  import Spinner from '@/Components/Spinner.vue'
  import Housing from '@/Models/Housing'

  const page = usePage()
  const housing = new Housing(page.props.housing)

  const emit = defineEmits(['loading', 'setProgress'])

  const props = defineProps({
    housing: {
      type: Object,
      required: true
    },
    loading: {
      type: Boolean,
      required: true
    },
    agent: {
      type: Object,
      required: false,
      default: () => null
    }
  })

  onMounted (async () => {
    emit('loading', false)
  });

  // Copies the text to the clipboard
  const copyToClipboard = async (text) => {
    try {
      await navigator.clipboard.writeText(text)
    } catch($e) {
    }
  }

  const getContent = (key) => {
    // search for key in housing.contents
    return housing.contents.value.find(content => content.data.name === key)?.data.content ?? ''
  }

  // Remove when GPT4-vision has function_calling
  const hasContentTexts = computed (() => {
    // check, if housing.contents has an entry with name = 'texts'
    return housing.contents.value.find(content => content.data.name === 'texts') !== undefined
  })
</script>

<template>
  <div>
    <Head title="Wohnung anzeigen" />

    <h1 class="text-2xl font-bold">{{ getContent('title') }}</h1>
    <div class="flex items-center space-x-2 mb-6">
      <div class="flex items-center">
        <svg class="w-3 h-3 mr-2 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
          <path d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z"/>
        </svg>
        <span class="text-sm font-semibold">5,0</span>
      </div>
      <p class="text-sm">· 5 Bewertungen ·</p>
      <div class="flex items-center">
        <svg class="w-3 h-3 mr-2 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
          <path d="m7.164 3.805-4.475.38L.327 6.546a1.114 1.114 0 0 0 .63 1.89l3.2.375 3.007-5.006ZM11.092 15.9l.472 3.14a1.114 1.114 0 0 0 1.89.63l2.36-2.362.38-4.475-5.102 3.067Zm8.617-14.283A1.613 1.613 0 0 0 18.383.291c-1.913-.33-5.811-.736-7.556 1.01-1.98 1.98-6.172 9.491-7.477 11.869a1.1 1.1 0 0 0 .193 1.316l.986.985.985.986a1.1 1.1 0 0 0 1.316.193c2.378-1.3 9.889-5.5 11.869-7.477 1.746-1.745 1.34-5.643 1.01-7.556Zm-3.873 6.268a2.63 2.63 0 1 1-3.72-3.72 2.63 2.63 0 0 1 3.72 3.72Z"/>
        </svg>
        <span class="text-sm font-semibold">Superhost</span>
      </div>
      <p class="text-sm">· {{ housing.data.address_street }} {{ housing.data.address_street_number }}, {{ housing.data.address_city }}, {{ housing.data.address_administrative_area_level_1  }}, {{ housing.data.address_country }}</p>
    </div>

    <!-- <div class="flex space-x-4 my-4 relative">
      <div class="w-1/2">
        <img :src="housing.images.value[0].data.path" alt="Hauptbild" class="rounded-lg shadow-md">
      </div>
      <div class="w-1/2 grid grid-cols-2 gap-1 sm:col-span-1">
        <img
          v-for="index in 4"
          :key="index"
          class="rounded-lg shadow-md"
          :src="housing.images.value[index].data.path" />
      </div>
    </div> -->

    <div v-if="hasContentTexts">
      <!-- Remove when GPT4-vision has function_calling -->
      <p v-html="getContent('texts').replace(/\n/g, '<br />')"></p>
    </div>

    <div v-else>
      <p>{{ getContent('description') }}</p>

      <h3 class="text-3xl font-bold dark:text-white mt-6 mb-2">Die Unterkunft</h3>
      <p>{{ getContent('accomodation') }}</p>

      <h3 class="text-3xl font-bold dark:text-white mt-6 mb-2">Gästezugang</h3>
      <p>{{ getContent('guest_accessibility') }}</p>

      <h3 class="text-3xl font-bold dark:text-white mt-6 mb-2">Weitere Angaben</h3>
      <p>{{ getContent('more') }}</p>
    </div>

    <div>
        <h3 class="text-3xl font-bold dark:text-white mt-6 mb-2">Bilder</h3>
      <div v-for="(image, index) in housing.images.value" :key="index" class="flex flex-col md:flex-row mb-12">
        <!-- Bildspalte -->
        <div class="md:w-1/2">
            <img :src="image.data.path" alt="Bild" class="rounded-lg shadow-md w-full" />
        </div>

        <!-- Text- und Buttonspalte -->
        <div class="md:w-1/2  p-2">
          <h4 class="text-xl font-bold dark:text-white mt-6 mb-2">{{ image.data.label }}</h4>
          <textarea class="w-full p-2 border border-gray-300 rounded-md" rows="4" placeholder="Ihr Text hier" v-model="image.data.description"></textarea>
          <div class="flex justify-end items-end mt-2">
            <button @click="copyToClipboard(image.data.description)" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded">Copy</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


