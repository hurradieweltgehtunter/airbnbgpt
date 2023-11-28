import { defineStore } from 'pinia'
import axios from 'axios'
import Housing from '@/Models/Housing'

export const useHousingStore = defineStore('housings', {
  state: () => ({
    housings: [],
    isLoading: false,
  }),
  getters: {
    // Fügen Sie hier notwendige Getter hinzu
  },
  actions: {
    async fetchHousings() {
      this.isLoading = true;
      try {
        const response = await axios.get('/api/housings');

        response.data.forEach((housingData) => {
          this.housings.push(new Housing(housingData));
        })

      } catch (error) {
        console.error('Es gab einen Fehler beim Laden der Housings:', error);
        // Hier können Sie auch Fehlerbehandlung einfügen
      } finally {
        this.isLoading = false;
      }
    },

    async createHousing(housingData) {
      let housing = new Housing(housingData);

      try {
          const response = await axios.post('/api/housings', housing.data);
          housing.data.id = response.data.id;
          this.housings.push(housing);
          return housing;
      } catch (error) {
          console.error('Es gab einen Fehler beim Erstellen des Housings:', error);
          // Hier können Sie auch Fehlerbehandlung einfügen
      }
    },

    async delete(housing) {
      try {
        await axios.delete('/api/housings/' + housing.data.id);
        this.housings.splice(this.housings.indexOf(housing), 1);
      } catch (error) {
        console.error('Es gab einen Fehler beim Löschen des Housings:', error);
        // Hier können Sie auch Fehlerbehandlung einfügen
      }
    },
  },
})
