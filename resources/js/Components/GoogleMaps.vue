<script setup>
    import { ref, watch, computed, onMounted } from 'vue';
    import { GoogleMap, Marker, InfoWindow } from "vue3-google-map";
    import GMapStyles from '@/themes/GoogleMaps.default.js'

    const zoom = ref(1)
    const props = defineProps({
        apiKey: {
            type: String,
            required: true
        },
        address: {
            type: Object,
            default: null,
        },
        style: {
            type: Object,
            default: () => ({
                width: "100%",
                height: "100%",
            }),
        },
    })
    const mapRef = ref(null)
    const interval = ref(null)

    const location = computed(() => {
        return {
            lat: parseFloat(props.address.lat),
            lng: parseFloat(props.address.lng)
        }
    })

    onMounted(() => {
        if(props.address)
            animateMap(location.value)
    })

    watch(() => location.value, (newVal, oldVal) => {
      if(oldVal.lat !== newVal.lat || oldVal.lng !== newVal.lng) {
        animateMap(newVal)
      }
    });

    const animateMap = (location) => {
        zoom.value = 15
        // // move map to new center
        // if (mapRef.value?.ready) {
        //     mapRef.value.map.panTo(location);
        // }

        // // animate zoom
        // interval.value = setInterval(() => {
        //     if(zoom.value >= 14) {
        //         clearInterval(interval.value)
        //         return
        //     }
        //     zoom.value = zoom.value + 1
        // }, 50);
    }
</script>

<template>
    <GoogleMap v-show="location.lat !== 0" ref="mapRef" :style="style"
        :api-key="apiKey"
        :center="location"
        :zoom="zoom"
        :styles="GMapStyles"
        :disableDefaultUI="true"
        :draggable="true"
        :fullscreenControl="false"
        :mapTypeControl="false"
        :streetViewControl="false"
        :zoomControl="true">

        <Marker v-if="location.lat !== 0" :options="{ position: location }" />
        <InfoWindow v-if="address.street !== ''" :options="{ position: location }"> {{ address.street }} {{ address.streetNumber }} <br />{{ address.zip }} {{ address.city }} </InfoWindow>
    </GoogleMap>
</template>
