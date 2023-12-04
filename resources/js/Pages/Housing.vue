<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router } from '@inertiajs/vue3';
  import { computed, onMounted, ref, defineAsyncComponent } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import Spinner from '@/Components/Spinner.vue'
  import Housing from '@/Models/Housing'
  import GoogleMaps from '@/Components/GoogleMaps.vue'
  import Progress from '@/Components/Progress.vue'
  import Agent from '@/Models/Agent'
  import PrimaryButton from '@/Components/UI/PrimaryButton.vue';

  const page = usePage()
  const loading = ref(false)

  const props = defineProps({
    section: {
      type: String,
      required: true
    },
    housing: {
      type: Object,
      required: false,
      default: () => new Housing()
    },
    agent: {
      type: Object,
      required: false,
      default: () => new Agent()
    }
  })
//   const agent = ref(new Agent(page.props.agent.data))
  const housing = ref(new Housing(page.props.housing))

  const GOOGLE_API_KEY = "AIzaSyC4JEY79cv6Ks4WwJLMP8vtAPybx8rbrmk"

  const loadingMessage = ref('Lass mich mal nachdenken…') // message to display while loading

  const progress = ref({
    'l': 0, // location
    's': 0, // surrounding
    't': 0, // type
    'f': 0, // furnishing
    'g': 0, // guest_expectations
  })

  const currentComponent = computed({
    get: () => {
      switch (props.section) {
        case 'create':
          return defineAsyncComponent(() => import('@/Components/Housing/Create.vue'))
        case 'show':
          return defineAsyncComponent(() => import('@/Components/Housing/Show.vue'))
        case 'run':
          return defineAsyncComponent(() => import('@/Components/Housing/Run.vue'))
        case 'editRooms':
          return defineAsyncComponent(() => import('@/Components/Housing/EditRooms.vue'))
        case 'images':
          return defineAsyncComponent(() => import('@/Components/Housing/Images.vue'))
        case 'showQuestionnaire':
          return defineAsyncComponent(() => import('@/Components/Housing/Questionnaire.vue'))
        case 'writingstyleSelect':
        return defineAsyncComponent(() => import('@/Components/Housing/WritingStyleSelect.vue'))
        default:
          return null
      }
    }
  })


  const addressComplete = computed(() => {
    return housing.value.addressIsComplete()
  })

  const setLoading = (value, message = null) => {
    loading.value = value
    if(message !== null) {
      loadingMessage.value = message
    }

    if(value === false) {
      loadingMessage.value = ''
    }
  }

  // Handler, if an progress update event is received
  const setProgress = (newProgress) => {
    progress.value = {
      ...progress.value,
      ...Object.fromEntries(
        Object.entries(newProgress).filter(([key, value]) => value > 0)
      )
    };
  }

</script>

<template>
  <BaseLayout>
    <div class="grid grid-cols-3 gap-4">
      <div class="col-span-3 sm:col-span-2 pr-4 border-r border-gray-200 relative">
        <transition name="fade" v-show="loading === true">
          <Spinner></Spinner>
        </transition>

        <transition v-show="loading === false">
          <component :is="currentComponent" :housing="housing" :loading="loading" @loading="setLoading" @setProgress="setProgress" :agent="agent"></component>
        </transition>
      </div>

      <div class="col-span-3 sm:col-span-1">
        <GoogleMaps v-if="addressComplete" :api-key="GOOGLE_API_KEY" :address="housing.getAddress()" style="width: 100%; height: 300px"></GoogleMaps>

        <Progress :progress="progress"></Progress>

        <!-- <PrimaryButton @click="resetHousing()">
          Von vorne beginnen
        </PrimaryButton> -->
      </div>
    </div>
  </BaseLayout>
</template>


<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 1s;
}
.fade-enter-from, .fade-leave-to {
  transition: opacity 1s;
  opacity: 0;
}

.textresult {
  initial-letter: 2;
}
</style>
