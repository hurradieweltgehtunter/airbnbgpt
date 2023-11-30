import { defineStore } from 'pinia'
import axios from 'axios'
import Housing from '@/Models/Housing'

export const useHousingStore = defineStore('housings', {
  state: () => ({
    housings: [],
    isLoading: true,
  }),
  getters: {
    // Fügen Sie hier notwendige Getter hinzu
  },
  actions: {
    fill(housings) {

      // housings must be of type array
      if (!Array.isArray(housings)) {
        throw new Error('HousingStore::fill: housings must be of type array')
      }

      this.housings = []

      housings.forEach(housing => {
        this.housings.push(new Housing(housing))
      })

      this.isLoading = false
    },
    async fetchHousings() {
      throw new Error('HousingStore::fetchHousings() is deprecated. Use fill() instead');

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
          const response = await axios.post('/housings', housing.data);
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
        await axios.delete('/housings/' + housing.data.id);
        this.housings.splice(this.housings.indexOf(housing), 1);
      } catch (error) {
        console.error('Es gab einen Fehler beim Löschen des Housings:', error);
        // Hier können Sie auch Fehlerbehandlung einfügen
      }
    },
  },
})
