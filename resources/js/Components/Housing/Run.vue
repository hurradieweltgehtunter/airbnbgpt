<script setup>
  import { onMounted, defineProps, ref } from 'vue';
  import { Head, router, usePage } from '@inertiajs/vue3'

  const page = usePage()
  const emit = defineEmits(['loading', 'setProgress'])

  const props = defineProps({
    housing: {
      type: Object,
      required: false
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

  const messages = ref([])

  const handleRoomReceived = (room) => {
    messages.value.push(room.name + ' erledigt')
  }

  onMounted(async () => {
    let requests = props.housing.agents.map((agent) => {
      agent.EE.on('room_received', handleRoomReceived);
      agent.run()
    })

    Promise.all(requests)
    .then(axiosResponses => {
      router.get('/housings/' + props.housing.data.id)
    })
    .catch(error => {
      console.error("Ein Fehler bei den Requests ist aufgetreten:", error);
    });
  })


</script>

<template>
  <div>
    <Head title="Inserat wird erstellt…" />
    <p v-for="(message, index) in messages" :key="index">{{ message }}</p>

  </div>

</template>
