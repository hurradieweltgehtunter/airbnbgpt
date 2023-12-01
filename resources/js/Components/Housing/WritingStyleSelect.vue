<script setup>
  import { ref, defineProps } from 'vue';
  import { Head, router } from '@inertiajs/vue3'
  import { usePage } from '@inertiajs/vue3'
  import Button from '@/Components/UI/Button.vue';
  import PageIntro from '@/Components/UI/PageIntro.vue';

  const page = usePage()

  const selectedWritingStyle = ref(null)
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

  const send = async () => {
    emit('loading', true, 'Ich schreibe deine Anzeige…')

    router.get('/housings/' + props.housing.data.id + '/prepare/' + selectedWritingStyle.value)
  }
</script>

<template>
  <form @submit.prevent="send" class="w-full flex flex-col h-full items-center">
    <Head title="Schreibstil wählen" />
    <PageIntro>
        <template #headline>Wähle deinen Stil – wie soll dein Inserat klingen?</template>
        <template #content>Fast geschafft! Jetzt kannst du den Schreibstil für dein Inserat auswählen. Ob locker und freundlich oder elegant und professionell – wähle einen Stil, der am besten zu dir und deiner Unterkunft passt. Diese Wahl beeinflusst, wie die KI dein Inserat formuliert, damit es genau deinen Vorstellungen entspricht.</template>
    </PageIntro>

    <div class="w-2/3 font-serif text-lg mb-12">
      <select id="writingStyles" v-model="selectedWritingStyle" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="null">Schreibstil wählen...</option>
        <option v-for="writingStyle in page.props.writingStyles.data" :key="writingStyle.id" :value="writingStyle.id" >{{ writingStyle.title }}</option>
      </select>
    </div>

    <Button variant="primary" type="submit" :disabled="selectedWritingStyle == null">Texte jetzt erstellen</button>

    <div id="informational-banner" tabindex="-1" class="mt-8 flex flex-col justify-between w-fullmd:flex-row">
        <h2 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Schreibstil - was ist das?</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Der Schreibstil ist der Ton und die Art, wie deine Inseratstexte formuliert werden. Er ist wie die Persönlichkeit deiner Texte – von professionell und sachlich bis hin zu locker und unterhaltsam. Dein gewählter Stil hilft unserer KI, die Texte so zu verfassen, dass sie genau zu dir und deiner Unterkunft passen.</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">Hast du noch keinen Schreibstil festgelegt? Kein Problem! <a :href="route('styles.create')" class="underline">Klicke hier</a>, um deinen eigenen Schreibstil zu definieren. Es ist einfach und macht Spaß! Du kannst verschiedene Stile erstellen und ausprobieren, was am besten zu deinem Angebot passt. Deine Gäste werden den Unterschied spüren!</p>

    </div>
  </form>
</template>

<style scoped>
button[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

</style>
