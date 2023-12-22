<script setup>
  import { onMounted, defineProps, ref, computed, watch } from 'vue';
  import { Head, router, usePage } from '@inertiajs/vue3'
  import Agent from '@/Models/Agent'

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
    },
  })

  const agents = ref([])

  onMounted(async () => {
    // transform the raw agent data to agent class
    page.props.agents.forEach((agent) => {
      agents.value.push(new Agent(agent))
    })

    startAgents()
  })

  /**
   * Get the number of running agents
   */
  const runningAgentsLength = computed(() => {
    return agents.value.filter((agent) => agent.finished === false).length
  })

  /**
   * Start all available agents
   */
  const startAgents = () => {
    const runningAgents = ref([])

    agents.value.forEach((agent) => {
      runningAgents.value.push(agent.run())
    })

    Promise.all(runningAgents.value)
      .then(() => {
        router.get(route('housings.show', props.housing.data.id))
      })
      .catch((error) => {
        console.log(error)
      })
  }

</script>

<template>
  <div class="h-full flex items-center justify-center">
    <Head title="Inserat wird erstellt…" />
    <div class="text-center">
      Dein Inserat wird jetzt erstellt. Bitte warte einen Moment.
      <p>{{ runningAgentsLength }} Tasks werden ausgeführt</p>
    </div>
  </div>

</template>
