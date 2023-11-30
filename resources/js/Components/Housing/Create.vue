<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router } from '@inertiajs/vue3';
  import { onMounted, ref } from 'vue';
  import AddressAutocomplete from '@/Components/GoogleAutocomplete.vue'
  import { useHousingStore } from '@/Store/Housing'

  const housingStore = useHousingStore()

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

  onMounted (async () => {

  });

  const GOOGLE_API_KEY = "AIzaSyC4JEY79cv6Ks4WwJLMP8vtAPybx8rbrmk"

  const handlePlace = async (place) => {
    let housing = await housingStore.createHousing(place)
    router.get('/housings/' + housing.data.id + '/images')
  }
</script>

<template>
  <div>
    <Head title="Unterkunft anlegen" />
    <AddressAutocomplete :api-key="GOOGLE_API_KEY" @placeReceived="handlePlace" placeholder="Gib die Adresse deiner Unterkunft ein"></AddressAutocomplete>
  </div>
</template>
