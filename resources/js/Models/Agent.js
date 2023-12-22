import { reactive, ref } from 'vue';

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
    return new Promise((resolve, reject) => {
      try {
        this.finished = false;
        axios.post(`/api/agents/${this.data.id}/run`, postData)
        .then(response => {
          this.finished = true;
          if(response && response.data)
            resolve(response.data);
          else
            resolve(true);
        })
      } catch (error) {
        reject(error);
      }
    })
  }
}

