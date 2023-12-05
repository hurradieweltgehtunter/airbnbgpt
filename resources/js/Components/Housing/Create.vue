<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router, usePage } from '@inertiajs/vue3';
  import { onMounted } from 'vue';
  import AddressAutocomplete from '@/Components/GoogleAutocomplete.vue'
  import { useHousingStore } from '@/Store/Housing'
  import PageIntro from '@/Components/UI/PageIntro.vue';

  const housingStore = useHousingStore()
  const page = usePage()
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

  const handlePlace = async (place) => {
    let housing = await housingStore.createHousing(place)
    router.get('/housings/' + housing.data.id + '/images')
  }
</script>

<template>
  <div>
    <Head title="Unterkunft anlegen" />
    <PageIntro>
      <template #headline>Lass uns starten – gib deine Adresse an!</template>
      <template #content>Hier beginnt dein Weg zu einem fantastischen AirBnB-Inserat! Gib uns bitte die Adresse deiner Unterkunft. Keine Sorge, sie wird nicht veröffentlicht. Die Adresse hilft unserer KI, die Umgebung deiner Unterkunft zu verstehen. Dadurch können wir einen auf dich zugeschnittenen Reiseführer erstellen, der Gästen die besten lokalen Highlights zeigt. Je genauer deine Angaben, desto persönlicher und attraktiver wird dein Inserat.</template>
    </PageIntro>

    <AddressAutocomplete :api-key="page.props.google_api_key" @placeReceived="handlePlace" placeholder="Gib die Adresse deiner Unterkunft ein"></AddressAutocomplete>

    <div id="informational-banner" tabindex="-1" class="mt-8 flex flex-col justify-between w-fullmd:flex-row">
      <h2 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Datenschutz? Nehmen wir ernst!</h2>
      <p class="flex items-center text-sm font-normal text-gray-500 dark:text-gray-400">Deine Adresse bleibt bei uns. Wir verwenden sie nur, um dein Inserat zu verbessern, und geben sie nicht weiter. Unsere Systeme sorgen dafür, dass deine Daten sicher sind. Und keine Sorge, die Adresse bleibt intern und wird nicht veröffentlicht.</p>
    </div>
  </div>
</template>
