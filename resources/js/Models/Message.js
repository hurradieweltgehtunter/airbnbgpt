import { reactive, ref } from 'vue';

export default class Message {
  constructor(data = {}) {
    this.data = reactive({
        id: null,
        content: "",
        sender_id: null,
        username: "",
        avatar: "",
        date: "",
        timestamp: "",
        isFinal: false,
        meta: {},
    });

    // Add properties of data to this.data if the property exists in this.data
    Object.keys(this.data).forEach(key => {
      if (data.hasOwnProperty(key)) {
        this.data[key] = data[key];
      }
    });
  }
}

