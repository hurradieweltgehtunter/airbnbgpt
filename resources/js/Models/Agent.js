import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3'
import EventEmitter from 'eventemitter3'

export default class Agent {
  constructor(data = {}) {

    this.data = reactive({
      id: null,
      entity: '',
      name: ''
    });

    // Add properties of data to this.data if the property exists in this.data
    Object.keys(this.data).forEach(key => {
      if (data.hasOwnProperty(key)) {
        this.data[key] = data[key];
      }
    });

    this.EE = new EventEmitter()
  }

  /**
   * Executes the agent
   */
  async run(postData = null) {
    try {
      if(this.data.name === 'ImageDescriptionAgent') {
        this.rooms = ref([]);

        const eventSource = new EventSource(`/api/agents/${this.data.id}/run`);

        eventSource.onmessage = (event) => {
          this.EE.emit('room_received', JSON.parse(event.data).room)
          this.rooms.push(event.data);
        };

        eventSource.onerror = (error) => {
            console.error('EventSource failed:', error);
            eventSource.close();
        };
      } else {
        const response = await axios.post(`/api/agents/${this.data.id}/run`, postData);
        return response.data;
      }
    } catch (error) {
      console.error(error);
    }
  }
}

