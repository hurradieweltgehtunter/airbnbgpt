<script setup>
  import { onMounted, ref, reactive, defineProps } from 'vue';
  import { Head } from '@inertiajs/vue3'
  import axios from 'axios'
  import { computed, nextTick } from 'vue'
  import { usePage } from '@inertiajs/vue3'
  import Message from '@/Models/Message'
  import Agent from '@/Models/Agent'

  const page = usePage()
  const user = computed(() => page.props.auth.user)
  const message = ref(new Message()) // Wrapper Object for user input
  const loadingMessage = ref('Lass mich mal nachdenken…') // message to display while loading
  const maxLength = 250 // max length of user input
  const textareaUserInput = ref(null); // ref to the textarea, used for autofocus
  const userInput = ref('') // user input of the freetext textarea
  const userInputBox = ref([]) // holds values if checkboxes are displayed (on multiple options)

  const emit = defineEmits(['loading', 'setProgress'])

  var agent = new Agent() // Holds the Questionnaire agent

  const props = defineProps({
    housing: {
      type: Object,
      required: true
    },
    loading: {
      type: Boolean,
      required: false,
      default: false
    }
  })

  onMounted(async () => {
    emit('loading', true)
    if(props.housing.hasAgent('HousingQuestionnaireAgent') === false) {
      agent = await props.housing.createAgent('HousingQuestionnaireAgent')
    } else {
      agent = props.housing.getAgent('HousingQuestionnaireAgent')
    }

    let response = await props.housing.runAgent('HousingQuestionnaireAgent')

    await handleResponse(response)
    emit('loading', false)

  })

  const handleEnterPress = (event) => {
    if (event.shiftKey) {
      // Wenn die Shift-Taste gedrückt wird, tue nichts (erlaube den Zeilenumbruch)
    } else {
      sendMessage()
    }
  }

  const handleButtonPress = (option) => {
    userInput.value = option
    sendMessage()
  }

  // Method to send a message
  const sendMessage = async () => {
    emit('loading', true, loadingMessage.value)

    if(userInputBox.value.length > 0) {
      userInput.value += "\n" + userInputBox.value.join(', ')
      userInputBox.value = []
    }

    if(userInput.value === '') {
      console.log('No input!')
      emit('loading', false)
      return
    }

    let date = new Date();
    let day = date.getDate();  // Gibt den Tag des Monats zurück
    let month = date.toLocaleString('default', { month: 'long' });  // Gibt den Namen des Monats zurück
    let hours = date.getHours().toString().padStart(2, '0');  // Gibt die Stunden zurück
    let minutes = date.getMinutes().toString().padStart(2, '0');  // Gibt die Minuten zurück

    let formattedTime = `${hours}:${minutes}`;  // Formatieren der Uhrzeit
    let formattedDate = `${day} ${month}`;  // Formatieren des Datums

    let userMessage = {
  	  senderId: user.value.id,
  	  content: userInput.value,
      date: formattedDate,
  	  timestamp: formattedTime,
    }

    let response = await axios.post(`/api/agents/${agent.data.id}/messages`, userMessage)

    response = await agent.run()
    if(await handleResponse(response) !== false)
        emit('loading', false)
  }

  const handleResponse = async (response) => {
    if(response.type !== 'message' && response.type !== 'redirect') { // redirect gets catched by axios interceptor
        console.error('Unknown response type: ', response.type)
        return false
    }

    message.value = new Message(response.message)

    userInput.value = ''
    await nextTick()

    if(textareaUserInput.value) {
      textareaUserInput.value.focus();
    }

    emit('setProgress', message.value.data.meta?.progress ?? {})
  }

  const hasFreetext = computed(() => {
    return true
    // return message.value.data.meta.hasFreetext
  })

  const options = computed(() => {
    return message.value.data.meta.options ?? []
  })

  const multipleOptionsAllowed = computed(() => {
    return message.value.data.meta.multipleOptions ?? false
  })

  const textareaPlaceholder = computed(() => {
    if(message.value.data.meta && message.value.data.meta?.options?.length > 0)
      return 'Oder verfasse deine eigene Antwort'
    else {
      return 'Deine Antwort'
    }
  })

  const textLength = computed(() => {
    return userInput.value.length
  })

</script>

<template>
  <form @submit.prevent="sendMessage" class="w-full flex flex-col h-full justify-between">
    <Head title="Fragebogen zur Unterkunft" />
    <div class="w-full flex justify-center items-center font-serif text-lg pt-12">
      <p class="w-2/3 font-normal text-gray-700 dark:text-gray-400 text-center" v-html="message.data.content"></p>
    </div>

    <div class="py-12">
      <div v-if="options.length > 0 && multipleOptionsAllowed === false" class="w-1/1 flex justify-center items-center flex-wrap">
        <button v-for="(option, index) in message.data.meta.options" :key="index" type="button" @click="handleButtonPress(option)" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">{{ option }}</button>
      </div>

      <div v-if="options.length > 0 && multipleOptionsAllowed === true" class="w-1/1">
        <div class="flex justify-center items-center">
          <ul class="justify-center items-center w-full flex flex-wrap text-sm font-medium text-gray-900 border sm:flex dark:text-white">
            <li v-for="(option, index) in options" :key="index" class="">
              <div class="flex items-center  px-3">
                  <input :id="'checkbox-' + option" type="checkbox" v-model="userInputBox" :value="option" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                  <label :for="'checkbox-' + option" class="w-full py-3 ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ option }}</label>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div v-if="hasFreetext">
      <div class="flex items-center py-2 rounded-lg w-full justify-start">
        <!-- <button type="button" class="inline-flex justify-center p-2 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 18">
            <path fill="currentColor" d="M13 5.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0ZM7.565 7.423 4.5 14h11.518l-2.516-3.71L11 13 7.565 7.423Z"/>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 1H2a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1Z"/>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0ZM7.565 7.423 4.5 14h11.518l-2.516-3.71L11 13 7.565 7.423Z"/>
          </svg>
          <span class="sr-only">Upload image</span>
        </button>
        <button type="button" class="p-2 text-gray-500 rounded-lg cursor-pointer hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600">
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.408 7.5h.01m-6.876 0h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM4.6 11a5.5 5.5 0 0 0 10.81 0H4.6Z"/>
          </svg>
          <span class="sr-only">Add emoji</span>
        </button> -->
        <div class="w-full">
          <textarea id="textareaUserInput" ref="textareaUserInput" oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"' v-model="userInput" @keypress.enter.prevent="handleEnterPress" rows="1" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" :placeholder="textareaPlaceholder" :maxlength="maxLength"></textarea>
          <span class="sr-only">Send message</span>
          <div class="text-xs text-gray-500 text-right">{{ textLength }}/{{ maxLength }} Zeichen</div>
        </div>
        <div>
          <button type="submit" class="inline-flex justify-center p-2 text-blue-600 rounded-full cursor-pointer hover:bg-blue-100 dark:text-blue-500 dark:hover:bg-gray-600 ml-2.5">
            <svg class="w-5 h-5 rotate-90" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
              <path d="m17.914 18.594-8-18a1 1 0 0 0-1.828 0l-8 18a1 1 0 0 0 1.157 1.376L8 18.281V9a1 1 0 0 1 2 0v9.281l6.758 1.689a1 1 0 0 0 1.156-1.376Z"/>
            </svg>
            <span class="sr-only">Send message</span>
          </button>
          <span>&nbsp;</span>
        </div>
      </div>
    </div>
  </form>
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
