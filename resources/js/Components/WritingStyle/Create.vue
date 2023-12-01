<script setup>
  import BaseLayout from '@/Layouts/Base2Layout.vue';
  import { Head, router } from '@inertiajs/vue3';
  import { onMounted, ref, defineEmits, computed } from 'vue';
  import WritingStyleExample from '@/Models/WritingStyleExample'
  import WritingStyle from '@/Models/WritingStyle'
  import Divider from '@/Components/UI/Divider.vue'
  import Button from '@/Components/UI/Button.vue'
  import PageIntro from '@/Components/UI/PageIntro.vue'
  import Spinner from '@/Components/Spinner.vue'
  import Toast from '@/Components/UI/Toast.vue'
  import Alert from '@/Components/UI/Alert.vue'

  const props = defineProps({
    section: {
      type: String,
      required: false
    },
    writingStyle: {
      type: Object,
      required: true,
    }
  })

  const emit = defineEmits(['loading'])

  const writingStyleExample = new WritingStyleExample()
  const writingStyle = new WritingStyle()
  const loading = ref(false)
  const styleAnalyzed = ref(false)

  onMounted (async () => {
    writingStyle.setData(props.writingStyle.data)
  });

  const isSaveable = computed (() => {
    return props.writingStyle.data.title.length < 1 || props.writingStyle.data.description.length < 1
  })

  const saveStyle = async () => {
    emit('loading', true)
    await props.writingStyle.save()
    router.get('/dashboard')
  }

  const runWritingStyleAnalyzer = async () => {
    loading.value = true
    let response = await axios.post(route('styles.analyzeExample'), { content: writingStyleExample.data.content})
    let ws = new WritingStyle(response.data)

    // Merge props.writingStyle.data and ws.data
    props.writingStyle.data = {...props.writingStyle.data, ...ws.data}
    styleAnalyzed.value = true

    scrollToTop()

    loading.value = false
  }

  // Animated scrolling to top
  const scrollToTop = () => {
    let scrollStep = -window.scrollY / (500 / 15),
    scrollInterval = setInterval(function(){
      if ( window.scrollY != 0 ) {
        window.scrollBy( 0, scrollStep );
      }
      else clearInterval(scrollInterval);
    },15);
  }

</script>

<template>
  <div>
    <Head title="Neuen Schreibstil anlegen" />

    <PageIntro>
      <template #headline>Neuen Schreibstil anlegen</template>
      <template #content>
        Willkommen im kreativen Bereich deines Inserats! Hier hast du die Möglichkeit, einen ganz eigenen Schreibstil für deine AirBnB-Anzeige zu kreieren. Denke an den Schreibstil als die Stimme deiner Unterkunft – er vermittelt deinen Gästen nicht nur Informationen, sondern auch Atmosphäre und Charakter.
        <br />
        Ob du eine gemütliche, familiäre Stimmung erzeugen oder deine Unterkunft als exklusives, modernes Reiseziel präsentieren möchtest, hier kannst du den Ton festlegen. Spiele mit Worten, wähle einen Stil, der zu dir passt, und lass unsere KI den Rest erledigen.
        <br />
        <br />
        <Alert variant="info">
            Vergiss nicht, du kannst mehrere Stile anlegen und sie z.B. je nach Anlass oder Saison wechseln. Experimentiere, habe Spaß und sieh zu, wie deine Worte Leben in dein Inserat bringen!
        </Alert>

      </template>
    </PageIntro>

    <Toast v-if="styleAnalyzed" class="mx-auto max-w-sm">Perfekt! Dein Schreibstil wurde ermittelt.</Toast>

    <div class="mb-6">
      <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Titel</label>
      <input type="text" id="title" v-model="props.writingStyle.data.title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    </div>

    <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Wie soll AirBnB-GPT deine Anzeige verfassen? Beschreibe den gewünschten Schreibstil.</label>
    <textarea id="description" v-model="props.writingStyle.data.description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder=""></textarea>

    <div class="flex justify-end items-center mt-2">
      <Button variant="primary" :disabled="isSaveable" @click="saveStyle()">Speichern</Button>
    </div>

    <divider />

    <PageIntro>
        <template #headline>Lass deine Worte sprechen - Ermittle deinen Schreibstil automatisch!</template>
        <template #content>
          Lass den Stil deiner Lieblingstexte in dein AirBnB-Inserat einfließen! Gib einfach einen oder mehrere Texte (mindestens 500 Zeichen) an, die du besonders ansprechend findest. Unsere KI analysiert diese Texte und ermittelt daraus einen Schreibstil, der dem Charakter und Ton deiner Beispiele entspricht.
          <br />
          <br />
          Dies könnte eine Text aus einer deiner bestehenden AirBnB Inserate sein, ein Ausschnitt aus einem Reiseblog sein oder ein Absatz aus deinem Lieblingsbuch - alles, was den Ton trifft, den du in deinem Inserat wiedergeben möchtest. Unsere Technologie erkennt die Nuancen und Eigenheiten dieser Texte und verwendet sie, um deinen ganz persönlichen Schreibstil zu formen.
          <br />
          <br />
          Nutze diese innovative Funktion, um deinem Inserat eine ganz persönliche Note zu geben, die deine Gäste anspricht und sich von anderen abhebt. Einfach, schnell und mit einem Ergebnis, das so einzigartig ist wie du und deine Unterkunft.
        </template>
    </PageIntro>

    <div class="relative">
      <textarea id="example" :disabled="loading" v-model="writingStyleExample.data.content" rows="10" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder=""></textarea>
      <Spinner v-if="loading" class="top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">Ermittle Schreibstil…</Spinner>
    </div>
    <div class="flex justify-end items-center mt-2 text-xs" :class="{'text-red-500 font-semibold': writingStyleExample.data.content.length < 500, 'text-green-500 font-semibold': writingStyleExample.data.content.length >= 500}">
      {{ writingStyleExample.data.content.length }} / 500 Zeichen
    </div>
    <div class="flex justify-end items-center mt-2">
      <Button :disabled="writingStyleExample.data.content.length <= 500 || loading" variant="primary" @click="runWritingStyleAnalyzer()">Text analysieren</Button>
    </div>
  </div>
</template>
