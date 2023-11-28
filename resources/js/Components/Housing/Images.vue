<script setup>
  import { Head, router } from '@inertiajs/vue3';
  import { computed, onMounted, ref } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import axios from 'axios'
  import Uppy from '@/Components/Uppy.vue'
  import Spinner from '@/Components/Spinner.vue'
  import Housing from '@/Models/Housing'
  import HousingRoom from '@/Models/HousingRoom'
  import HousingImage from '@/Models/HousingImage'
  import Agent from '@/Models/Agent'
  import Button from '@/Components/UI/Button.vue';

  import { useHousingStore } from '@/Store/housing'

  const housingStore = useHousingStore()
  const errormessage = ref('')
  const page = usePage()
  const housing = new Housing(page.props.housing)
  const uploadComplete = ref(false)
  const images = ref([])
  const rooms = ref([]) // Holds all rooms of the housing
  const step = ref('upload')
  const loading = ref(false)

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

  // Uppy config
  const XHRconfig = computed(() => ({
    endpoint: '/api/housings/' + housing.data.id + '/images' ,
    formData: true,
    headers: {
      'X-CSRF-Token': page.props.auth.csrf_token
    }
  }))

  const onUploadSuccess = (file, response) => {
    housing.images.value.push(new HousingImage(response.body))
  }

  // -------

  const saveImage = async (image) => {
    await axios.put('/api/housings/' + housing.data.id + '/images/' + image.data.id, {
      label: image.data.label
    })
  }

  // Create room to each image
  const createRooms = async () => {

    // save each image
    for(let image of housing.images.value) {
      await saveImage(image)
    }

    // if one image has no label, abort
    if(housing.images.value.some(image => image.data.label === '')) {
      errormessage.value = 'Bitte gib jedem Bild ein Label.'
      return
    }

    emit('loading', true)

    let response = await axios.patch('/api/housings/' + housing.data.id + '/rooms')
    rooms.value = response.data

    housing.rooms.value = rooms.value.map(room => {
      return new HousingRoom(room)
    })

    await runImageAnalyzerAgent()

  }

  const runImageAnalyzerAgent = async () => {
    loading.value = true
    let agent = await props.housing.createAgent('ImageAnalyzerAgent')
    await agent.run() // Should return a redirect to questionnaire
  }

  const isComplete = computed(() => {
    // check each label of each image. If one has an empty label, return false
    return housing.images.value.every(image => image.data.label !== '') && housing.images.value.length > 0
  })
</script>

<template>
  <div>
    <Head title="Bilder zur Unterkunft hinzufügen" />
    <Uppy :onUploadSuccess="onUploadSuccess" :meta="{ 'housingId': housing.id }" :XHRconfig="XHRconfig" :maxFiles="10"></Uppy>

    <form @submit.prevent="createRooms">
        <div class="flex justify-end items-center my-4">
          <Button type="submit" :disabled="isComplete !== true" :style="'primary'">Bilder analysieren</Button>
          <p>{{ errormessage }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col relative" v-for="(image, index) in housing.images.value" :key="image.data.id"> <!-- Add relative here -->
            <button @click="housing.deleteImage(image)" class="absolute bg-gray-200 bg-opacity-25 hover:bg-opacity-75 top-2 right-2 text-white p-2 transition-colors rounded">
              <svg class="w-3 h-3 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z"/>
              </svg>
            </button>

            <div class="bg-cover bg-center h-60" :style="'background-image: url(' + image.data.path + ');'"></div>
            Welches Zimmer ist das?
            <div class="flex flex-col sm:flex-row gap-4">
              <input v-model="image.data.label" class="flex-1 sm:flex-auto sm:w-2/3 p-2 border border-gray-300 rounded" type="text" placeholder="Wohnzimmer, Schlafzimmer, Terrasse, ..." :tabindex="index + 1">
            </div>
          </div>
        </div>
    </form>
  </div>
</template>


