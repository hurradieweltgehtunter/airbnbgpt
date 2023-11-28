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

  import { useHousingStore } from '@/Store/housing'

  const housingStore = useHousingStore()

  const page = usePage()
  const housing = new Housing(page.props.housing)
  const uploadComplete = ref(false)
  const images = ref([])
  const rooms = ref([]) // Holds all rooms of the housing
  const step = ref('upload')
  const loading = ref(false)

  const allimages = computed(() => {
    let img = housing.rooms.value.reduce((images, room) => {
      // Annahme: room.images ist bereits ein Array; fügen Sie es der Gesamtliste hinzu
      return images.concat(room.images);
    }, []); // Beginnen Sie mit einem leeren Array

    // add images to img
    img = img.concat(images.value)

    return img
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
    images.value.push(new HousingImage(response.body))
  }

  // -------

  const saveImage = async (image) => {
    await axios.put('/api/housings/' + housing.data.id + '/images/' + image.data.id, {
      label: image.data.label
    })
  }

  const createRooms = async () => {
    loading.value = true

    rooms.value = await axios.patch('/api/housings/' + housing.data.id + '/rooms')

    housing.rooms.value = rooms.value.map(room => {
      return {
        id: room.id,
        name: room.name,
        description: room.description,
        images: room.images.map(image => {
          return new HousingImage(image)
        })
      }
    })

    loading.value = false
    await runImageAnalyzerAgent()

    router.push({ name: 'housing.quest', params: { id: housing.data.id } })
  }

  const runImageAnalyzerAgent = async () => {
    loading.value = true
    let agent = await housing.createAgent('ImageAnalyzer')
    let agentResponse = await agent.run()

    // Update housing
    housing.rooms.value = agentResponse.map(room => {
      return new HousingRoom(room)
    })

    loading.value = false
  }

  const finish = async () => {

  }
</script>

<template>
  <div>
    <template v-if="step === 'upload'">
      <Uppy :onUploadSuccess="onUploadSuccess" :meta="{ 'housingId': housing.id }" :XHRconfig="XHRconfig"></Uppy>

      <div v-if="step === 'upload'" class="grid grid-cols-3 gap-4">
        <div class="flex flex-col relative" v-for="image in allimages" :key="image.data.id"> <!-- Add relative here -->
          <button @click="housing.deleteImage(image)" class="absolute bg-gray-200 bg-opacity-25 hover:bg-opacity-75 top-2 right-2 text-white p-2 transition-colors rounded">
            <svg class="w-3 h-3 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z"/>
            </svg>
          </button>

          <div class="bg-cover bg-center h-60" :style="'background-image: url(' + image.data.path + ');'"></div>
          Welches Zimmer ist das?
          <div class="flex flex-col sm:flex-row gap-4">
            <input v-model="image.data.label" class="flex-1 sm:flex-auto sm:w-2/3 p-2 border border-gray-300 rounded" type="text" placeholder="Dein Text hier...">
            <button @click="saveImage(image)" class="flex-1 sm:flex-auto sm:w-1/3 bg-peachPink-500 text-white p-2 hover:bg-peachPink-800 transition-colors rounded">Speichern</button>
          </div>
        </div>
      </div>

      <button @click="createRooms()" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded mt-2">Bilder analysieren</button>
    </template>

    <template v-if="step === 'texts'">
      <div v-for="room in rooms" :key="room.id" class="mb-12">
        <label :for="'description-' + room.id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ room.name }}</label>
        <textarea :for="'description-' + room.id" v-model="room.description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your thoughts here..."></textarea>
      </div>
      <button @click="finish()" class="bg-peachPink-500 hover:bg-peachPink-800 text-white p-2 rounded mt-2">Texte ok, let's go</button>
    </template>

  </div>
</template>


