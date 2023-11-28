import { reactive, ref } from 'vue';
import HousingImage from './HousingImage';

export default class HousingRoom {
  constructor(data) {

    this.data = reactive({
      id: null,
      housing_id: null,
      name: "",
      description: ""
    });

    // Add properties of data to this.data if the property exists in this.data
    Object.keys(this.data).forEach(key => {
      if (data.hasOwnProperty(key)) {
        this.data[key] = data[key];
      }
    });

    this.images = ref([])

    if(data.images) {
      this.images.value = data.images.map(image => new HousingImage(image));
    }
  }
}

