import { reactive, ref } from 'vue';
import { toast } from 'vue3-toastify';

export default class Agent {
  constructor(data = {}) {
    this.finished = false;
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
  }

  /**
   * Executes the agent
   */
  async run(postData = null) {
    this.finished = false
    let response
    try {
      response = await axios.post(`/api/agents/${this.data.id}/run`, postData)
    } catch (error) {
      switch(error.response.status) {
        case 408:
          toast.error('Agent timed out')
          console.log('Agent timed out')
          break;
        case 404:
          toast.error('Agent not found')
          console.log('Agent not found')
          break;
        case 500:
          toast.error('Agent error')
          console.log('Agent error')
          break;
        default:
          toast.error('Unknown error')
          console.log('Unknown error')
      }
      this.finished = true;
      return false
    }

    this.finished = true;
    if(response && response.data)
        return response.data
      else
        return true
  }
}
