import { reactive, ref} from 'vue';
import Agent from './Agent';

export default class WritingStyleExample {
  constructor(data = {}) {
    this.data = reactive({
      id: null,
      writing_style_id: null,
      content: '',
      description: ''
    });

    // Add properties of data to this.data if the property exists in this.data
    Object.keys(this.data).forEach(key => {
      if (data.hasOwnProperty(key)) {
        this.data[key] = data[key];
      }
    });
  }

  async create(writingStyleId) {
    try {
      const response = await axios.post(`/api/writingstyles/${writingStyleId}/exmaples`);
      return response.data;
    } catch (error) {
      console.error(error);
    }
  }

  async delete() {
    try {
      const response = await axios.delete(`/api/housings/${this.data.id}`);
      return response.data;
    } catch (error) {
      console.error(error);
    }
  }

  async deleteImage(image) {
    try {

      const response = await axios.delete(`/api/housings/${this.data.id}/images/${image.data.id}`);

      // Remove image from images array
      const index = this.images.value.indexOf(image);

      if (index > -1) {
        this.images.value.splice(index, 1);
      }


      return true;
    } catch (error) {
      console.error(error);
    }
  }

  async createRoom(roomName) {
    try {
      const response = await axios.post(`/api/housings/${this.data.id}/rooms`, {
        name: roomName
      });

      this.rooms = new HousingRoom(response.data)

      return response.data;
    } catch (error) {
      console.error(error);
    }
  }

  async createAgent(agentName) {
    console.log('createAgent', agentName, this.data.id)

    let response = await axios.post('/api/housings/' + this.data.id + '/createagent', {
      name: agentName
    })
    console.log('response', response.data)

    let agent = new Agent(response.data)

    console.log('agent', agent)

    return agent
  }
}

