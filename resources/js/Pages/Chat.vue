<script setup>
    import { register } from 'vue-advanced-chat'
    import BaseLayout from '../Layouts/Base2Layout.vue';
    import { onMounted, ref, reactive } from 'vue';
    import { router } from '@inertiajs/vue3'
    import axios from 'axios'
    import { computed } from 'vue'
    import { usePage } from '@inertiajs/vue3'
    import {defaultThemeStyles} from '@/themes/chat.js'

    register()

    const page = usePage()
    const user = computed(() => page.props.auth.user)
    const messagesLoaded = ref(false)
    const roomsLoaded = ref(false)
    const messages = ref([])
    const currentUserId = ref(user.value.id)
    const rooms = ref()
    const conversationRunning = ref(false) // Used to prevent multiple conversations at the same time
    const progress = ref({
      'location': 0,
      'type': 0,
      'furnishing': 0,
      'guest_expectations': 0,
      'writingstyle': 0,
    })

    onMounted(async () => {
      await loadRooms()
      await loadMessages(rooms.value[0].roomId )
    })

    // Method to load the chat room
    const loadRooms = async () => {
      try {
        let response = await axios.get('/api/rooms')

        // rooms.value = response.data
        rooms.value = response.data

      } catch (error) {
        console.log('Error while fetching rooms!', error)
      }

      roomsLoaded.value = true

    }

    // Method to load all messages in a chat room
    const loadMessages = async (roomId) => {
      messagesLoaded.value = false

      try {
        let response = await axios.get('/api/rooms/' + roomId + '/messages')
        messages.value = response.data.data
      } catch (error) {
        console.log('Error while fetching rooms!', error)
      }

      messagesLoaded.value = true
    }

    // Method to send a message
    const sendMessage = async ({ roomId, content, files, replyMessage, usersTag }) => {
      conversationRunning.value = true
      let date = new Date();
      let day = date.getDate();  // Gibt den Tag des Monats zurück
      let month = date.toLocaleString('default', { month: 'long' });  // Gibt den Namen des Monats zurück
      let hours = date.getHours().toString().padStart(2, '0');  // Gibt die Stunden zurück
      let minutes = date.getMinutes().toString().padStart(2, '0');  // Gibt die Minuten zurück

      let formattedTime = `${hours}:${minutes}`;  // Formatieren der Uhrzeit
      let formattedDate = `${day} ${month}`;  // Formatieren des Datums

      let message = {
        _id: '1',
      	senderId: currentUserId.value.toString(),
      	content: content,
        date: formattedDate,
      	timestamp: formattedTime,
        usersTag: usersTag,
        roomId: roomId,
        replyMessage: replyMessage,
        saved: true,
        distributed: false,
        seen: false
      }

      messages.value = [
        ...messages.value,
        message
      ]

      if (files) {
      	message.files = formattedFiles(files)
      }

      let response = await axios.post('/api/messages', message)

      let newMessages = messages.value
      newMessages.pop()

      messages.value = [
        ...newMessages,
        response.data
      ]

      if (files) {
        for (let index = 0; index < files.length; index++) {
          await uploadFile({ file: files[index], messageId: response.data._id, roomId })
        }
      }

      rooms.value[0]['typingUsers'] = [...rooms.value[0].typingUsers, '1']

      message = {
        _id: '1',
      	senderId: '1',
      	content: '…_thinking_…',
        system: true,
      	timestamp: formattedTime,
        usersTag: usersTag,
        roomId: roomId,
        replyMessage: replyMessage,
        saved: false,
        distributed: false,
        seen: false
      }

      messages.value = [
        ...messages.value,
        message
      ]

      response = await axios.post('/api/messages/next', {
        roomId: rooms.value[0].roomId
      })

      // remove the last entry in messages
      let messagesTemp = messages.value
      messagesTemp.pop()
      messages.value = messagesTemp

      messages.value = [
        ...messages.value,
        response.data
      ]

      // Update progress
      progress.value = { ...progress.value, ...response.data.progress };

      rooms.value[0]['typingUsers'] = []
      conversationRunning.value = false
    }

    const formattedFiles = (files) => {
      const formattedFiles = []

      files.forEach(file => {
        const messageFile = {
          name: file.name,
          size: file.size,
          type: file.type,
          extension: file.extension || file.type,
          url: file.url || file.localUrl
        }

        if (file.audio) {
          messageFile.audio = true
          messageFile.duration = file.duration
        }

        formattedFiles.push(messageFile)
      })

      return formattedFiles
    }

    const uploadFile = async ({ file, messageId, roomId }) => {
      return new Promise(resolve => {
        let type = file.extension || file.type
        if (type === 'svg' || type === 'pdf') {
          type = file.type
        }

        let formData = new FormData();
        formData.append('file', file.blob);
        formData.append('messageId', messageId);
        formData.append('roomId', roomId);
        console.log('>> formData >> ', formData);

        // You should have a server side REST API
        axios.post('/api/upload',
          formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
          ).then(function () {
            console.log('SUCCESS!!');
          })
          .catch(function () {
            console.log('FAILURE!!');
          });
      })
    }

    const sendMessageReaction = ({ roomId, messageId, reaction, remove }) => {
      console.log('roomId', roomId)
      console.log('messageId', messageId)
      console.log('reaction', reaction)
      console.log('remove', remove)
    }

    const messageActions = []
    const autoScroll = ref({
      send: {
        new: true, // will scroll down after sending a message
        newAfterScrollUp: false // will not scroll down after sending a message when scrolled up
      },
      receive: {
        new: true, // will scroll down when receiving a message
        newAfterScrollUp: true // will scroll down when receiving a message when scrolled up
      }
    })

    const acceptedFiles = ref ("image/png, image/jpeg, application/pdf")
    const roomActions = [
      { name: 'inviteUser', title: 'Invite User' },
      { name: 'removeUser', title: 'Remove User' },
      { name: 'deleteRoom', title: 'Delete Room' }
    ]

    const resetRoom = async (roomId) => {
      try {
        await axios.delete('/api/rooms/' + roomId)
        await loadRooms()
        await loadMessages(rooms.value[0].roomId)

      } catch (error) {
        console.log('Error while deleting room!', error)
      }
    }
</script>

<template>
  <BaseLayout>
    <div class="grid grid-cols-5 gap-4">
      <div class="col-span-4 p-4">
        <vue-advanced-chat
          height="calc(100vh - 20rem)"
          :current-user-id="currentUserId"
          :rooms="JSON.stringify(rooms)"
          :messages="JSON.stringify(messages)"
          :room-actions="JSON.stringify(roomActions)"
          :rooms-loaded="roomsLoaded"
          :messages-loaded="messagesLoaded"
          :rooms-list-opened="false"
          :loading-rooms="!roomsLoaded"
          :message-actions="JSON.stringify(messageActions)"
          :auto-scroll="JSON.stringify(autoScroll)"
          :single-room="true"
          :accepted-files="acceptedFiles"
          @send-message="sendMessage($event.detail[0])"
          @send-message-reaction="sendMessageReaction($event.detail[0])"
          :styles="JSON.stringify(defaultThemeStyles)"
        />

        <button type="button" @click="resetRoom(rooms[0].roomId)" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
          Reset
        </button>
      </div>
      <div class="col-span-1 p-4">


        <template v-for="(progress, index) in progress" :key="index">
          <div class="flex justify-between mb-1">
            <span class="text-base font-medium text-blue-700 dark:text-white">{{ index }}</span>
            <span class="text-sm font-medium text-blue-700 dark:text-white">{{ progress }} %</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div class="bg-blue-600 h-2.5 rounded-full" :style="'width: ' + progress + '%'"></div>
          </div>
        </template>
      </div>
    </div>
  </BaseLayout>


</template>
