<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head } from '@inertiajs/vue3';
  import { onMounted, ref, computed, defineAsyncComponent } from 'vue';
  import { usePage } from '@inertiajs/vue3'

  const loading = ref(false);
  const page = usePage()

  onMounted (async () => {
  });

  const props = defineProps({
    section: {
      type: String,
      required: false,
      default: 'index'
    }
  })

  const currentComponent = computed({
    get: () => {
      switch (props.section) {
        case 'index':
          return defineAsyncComponent(() => import('@/Components/Admin/Agent/ShowAll.vue'))
          break;
        // case 'create':
        //   return defineAsyncComponent(() => import('@/Components/Admin/Agent/Create.vue'))
        case 'show':
          return defineAsyncComponent(() => import('@/Components/Admin/Agent/Show.vue'))
        // case 'write':
        //   return defineAsyncComponent(() => import('@/Components/Admin/Agent/Write.vue'))
        // case 'editRooms':
        //   return defineAsyncComponent(() => import('@/Components/Admin/Agent/EditRooms.vue'))
        // case 'uploadImages':
        //   return defineAsyncComponent(() => import('@/Components/Admin/Agent/UploadImages.vue'))
        // case 'showQuestionnaire':
        //   return defineAsyncComponent(() => import('@/Components/Admin/Agent/Questionnaire.vue'))
        default:
          return null
      }
    }
  })

</script>

<template>
  <Head title="Agent Dashboard" />

  <BaseLayout>
    <component :is="currentComponent"></component>

  </BaseLayout>
</template>
