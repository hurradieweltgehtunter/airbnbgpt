import { defineStore } from 'pinia'
import axios from 'axios'
import WritingStyle from '@/Models/WritingStyle'

export const useWritingStyleStore = defineStore('writingStyles', {
  state: () => ({
    writingStyles: [],
    isLoading: false,
  }),
  getters: {
    // Fügen Sie hier notwendige Getter hinzu
  },
  actions: {
    async fetchAll() {
      this.isLoading = true;
      try {
        const response = await axios.get('/api/writingstyles');

        response.data.forEach((writingStyleData) => {
          this.writingStyles.push(new WritingStyle(writingStyleData));
        })

      } catch (error) {
        console.error('Es gab einen Fehler beim Laden der writingStyles:', error);
        // Hier können Sie auch Fehlerbehandlung einfügen
      } finally {
        this.isLoading = false;
      }
    },
    async delete(writingStyle) {
        try {
          await writingStyle.delete();
          this.writingStyles.splice(this.writingStyles.indexOf(writingStyle), 1);
        } catch (error) {
          console.error('Es gab einen Fehler beim Löschen des writingStyles:', error);
          // Hier können Sie auch Fehlerbehandlung einfügen
        }
    },
    async create(writingStyleData) {
      let writingStyle = new WritingStyle(writingStyleData);
      try {
          const response = await axios.post('/api/writingstyles', writingStyle.data);
          writingStyle.data.id = response.data.id;
          this.writingStyles.push(writingStyle);
          return writingStyle;
      } catch (error) {
          console.error('Es gab einen Fehler beim Erstellen des writingStyles:', error);
          // Hier können Sie auch Fehlerbehandlung einfügen
      }
    }
  },
})
