<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router } from '@inertiajs/vue3';
  import { onMounted, ref } from 'vue';
  import AddressAutocomplete from '@/Components/GoogleAutocomplete.vue'
  import { useHousingStore } from '@/Store/housing'

  const housingStore = useHousingStore()

  onMounted (async () => {

  });

  const GOOGLE_API_KEY = "AIzaSyC4JEY79cv6Ks4WwJLMP8vtAPybx8rbrmk"

  const handlePlace = async (place) => {
    let housing = await housingStore.createHousing(place)
    router.get('/housings/' + housing.data.id)
  }
</script>

<template>
    <Head title="Create Housing" />

    <BaseLayout>
      <AddressAutocomplete :api-key="GOOGLE_API_KEY" @placeReceived="handlePlace" placeholder="Gib die Adresse deiner Unterkunft ein"></AddressAutocomplete>
    </BaseLayout>
</template>
