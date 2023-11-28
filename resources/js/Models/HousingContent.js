import { reactive } from 'vue';

export default class HousingContent {
  constructor(data) {
    this.data = reactive({
      id: null,
      housing_id: null,
      name: '',
      content: '',
      created_at: '',
      updated_at: ''
    });

    // Add properties of data to this.data
    Object.assign(this.data, data);

  }

  // Eventuell benötigen Sie Getter, um auf die reaktiven Daten zuzugreifen
  get name() {
    return this.data.name;
  }

  set name(value) {
    this.data.name = value;
  }

  async delete() {
    try {
      const response = await axios.delete(`/api/housingimages/${this.data.id}`);
      return response.data;
    } catch (error) {
      console.error(error);
    }
  }
}

