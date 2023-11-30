import { reactive, ref} from 'vue';
import Agent from './Agent';
import WritingStyleExample from './WritingStyleExample';

export default class WritingStyle {
  constructor(data = {}) {
    this.data = reactive({
      id: null,
      title: '',
      description: ''
    });

    this.setData(data)
  }

  setData (data) {
    this.examples = ref([])

    // Add properties of data to this.data if the property exists in this.data
    Object.keys(this.data).forEach(key => {
      if (data.hasOwnProperty(key)) {
        this.data[key] = data[key];
      }
    });

    if(data.examples) {
      this.examples.value = data.examples.map(example => new WritingStyleExample(example));
    }
  }

  async create (data = {}) {
    try {
      const response = await axios.post(route('styles.store'), data);
      // Add properties of data to this.data if the property exists in this.data
      Object.keys(this.data).forEach(key => {
        if (response.data.hasOwnProperty(key)) {
          this.data[key] = response.data[key];
        }
      });

    } catch (error) {
      console.error(error);
    }
  }

  async save () {
    try {
        if(this.data.id) {
            await this.update()
        } else {
            const response = await this.create(this.data)
        }
    } catch (error) {
      console.error(error);
    }
  }

  async update () {
    try {

      await axios.put(route('writingstyles.update', this.data.id), this.data);

    } catch (error) {
      console.error(error);
    }
  }

  async createExample (example) {
    try {
      const response = await axios.post(`/api/writingstyles/${this.data.id}/examples`, {
        content: example
      });

      this.examples.value.push(new WritingStyleExample(response.data))
    } catch (error) {
      console.error(error);
    }
  }

  async delete() {
    try {
      const response = await axios.delete(`/api/writingstyles/${this.data.id}`);
      return response.data;
    } catch (error) {
      console.error(error);
    }
  }

  async createAgent() {
    let response = await axios.get('/api/writingstyles/' + this.data.id + '/createagent')

    let agent = new Agent(response.data.data)

    return agent
  }
}
