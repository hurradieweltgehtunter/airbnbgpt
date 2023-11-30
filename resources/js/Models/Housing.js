import { reactive, ref} from 'vue';
import HousingRoom from './HousingRoom';
import HousingImage from './HousingImage';
import HousingContent from './HousingContent';
import Agent from './Agent';

export default class Housing {
  constructor(data = {}) {
    this.data = reactive({
      id: null,
      name: '',
      address_street_number: '',
      address_street: '',
      address_city: '',
      address_zip: '',
      address_country: '',
      address_sublocality: '',
      address_sublocality_level_1: '',
      address_administrative_area_level_1: '',
      lat: null,
      lng: null,
      created_at: null,
      is_finished: null,
    });

    this.rooms = ref([])
    this.agents = ref([])
    this.contents = ref([])
    this.images = ref([])

    // Add properties of data to this.data if the property exists in this.data
    Object.keys(this.data).forEach(key => {
      if (data.hasOwnProperty(key)) {
        this.data[key] = data[key];
      }
    });

    if(data.rooms) {
      this.rooms.value = data.rooms.map(room => new HousingRoom(room));
    }

    if(data.agents) {
      this.agents.value = data.agents.map(agent => new Agent(agent));
    }

    if(data.contents) {
        this.contents.value = data.contents.map(content => new HousingContent(content));
    }

    if(data.images) {
        this.images.value = data.images.map(image => new HousingImage(image));
    }
  }

  // Eventuell benötigen Sie Getter, um auf die reaktiven Daten zuzugreifen
  get name() {
    return this.data.name;
  }

  set name(value) {
    this.data.name = value;
  }

  // Ähnlich für andere Eigenschaften ...
  setAddress(address) {
    console.log('setAddress', address)

  }

  getAddressString() {
    if(this.data == null) return ''
    return `${this.data.address_street} ${this.data.address_street_number}, ${this.data.address_zip} ${this.data.address_city}`;
  }

  getAddress() {
    return {
      street: this.data.address_street,
      streetNumber: this.data.address_street_number,
      city: this.data.address_city,
      zip: this.data.address_zip,
      lat: this.data.lat,
      lng: this.data.lng,
    }
  }

  get created_at() {
    let date = new Date(this.data.created_at);
    return date.toLocaleDateString('de-DE');
  }

  addressIsComplete() {
    return this.data.address_street && this.data.address_street_number && this.data.address_city && this.data.address_zip && this.data.lat && this.data.lng;
  }

  async deleteImage(image) {
    try {

      const response = await axios.delete(route('housingimages.destroy', image.data.id));

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

  hasAgent(agentName) {
    // Check, if this.agents contains an agent with the given name
    let found = this.agents.find(agent => agent.data.name === agentName)

    if(found === undefined) {
      return false
    } else {
      return true
    }
  }

  getAgent(agentName) {
    // Check, if this.agents contains an agent with the given name
    let found = this.agents.find(agent => agent.data.name === agentName)

    if(found === undefined) {
      return false
    } else {
      return found
    }
  }

  async createAgent(agentName) {
    let response = await axios.post('/api/housings/' + this.data.id + '/createagent', {
      name: agentName
    })

    let agent = new Agent(response.data.data)

    this.agents.push(agent)

    return agent
  }

  async runAgent(agentName) {
    let agent = this.agents.find(agent => agent.data.name === agentName)
    if(agent === undefined) {
      return false
    } else {
      return agent.run()
    }
  }
}

