<template>
  <input ref="input" id="autocomplete-input" />
</template>

<script setup>
  import { onMounted, defineProps, ref, defineEmits } from "vue"
  import { Loader } from "@googlemaps/js-api-loader"

  const emit = defineEmits(['placeReceived'])

  const service = ref(null)

  const props = defineProps({
    apiKey: {
      type: String,
      required: true
    }
  })

  const userinput = ref('')

/**
 * if address is already confirmed:
 * https://developers.google.com/maps/documentation/javascript/places?hl=de#place_details_requests
 *
 * service = new google.maps.places.PlacesService(map);
service.getDetails(request, callback);

 */

  onMounted(() => {
    const loader = new Loader({
      apiKey: props.apiKey,
      version: "weekly",
    //   ...additionalOptions,
    });

    loader.load().then(async () => {
      const input = document.getElementById("autocomplete-input");
      await google.maps.importLibrary("places");

      const autocomplete = new google.maps.places.Autocomplete(input, {
        fields: ["address_components", "geometry", "place_id"],
        types: ["address"],
      });

      autocomplete.addListener('place_changed', function () {
        // marker.setVisible(false);
        const place = autocomplete.getPlace();
        if (!place.geometry) {
          // User entered the name of a Place that was not suggested and
          // pressed the Enter key, or the Place Details request failed.
          window.alert('No details available for input: \'' + place.name + '\'');
          return;
        }

        // format the raw google result to a key->value object
        const rearranged = {};
        place.address_components.forEach(component => {
          const type = component.types[0];
          rearranged[type] = component.long_name;
        });

        const formattedPlace = {}
        formattedPlace.address_street_number = rearranged.street_number
        formattedPlace.address_street = rearranged.route
        formattedPlace.address_city = rearranged.locality
        formattedPlace.address_zip = rearranged.postal_code
        formattedPlace.address_country = rearranged.country
        formattedPlace.address_sublocality = rearranged.sublocality ?? ''
        formattedPlace.address_sublocality_level_1 = rearranged.sublocality_level_1 ?? '' // Stadtteil
        formattedPlace.address_administrative_area_level_1 = rearranged.administrative_area_level_1 ?? '' // Bundesland

        formattedPlace.lat = parseFloat(place.geometry.location.lat())
        formattedPlace.lng = parseFloat(place.geometry.location.lng())

        emit('placeReceived', formattedPlace);
      });
    });
  });
</script>

<style>
  input {
    width: 100%;
  }

  .pac-icon {
    display: none;
  }

  .pac-container, .pac-container * {
    font-family: Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji" !important;
  }
</style>
