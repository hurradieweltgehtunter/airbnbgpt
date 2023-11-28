<template>
    <div class="progress my-3">
      <template v-for="(progress, topic) in props.progress" :key="topic">
        <div class="mb-2">
          <div class="flex justify-between mb-1">
            <span class="text-base font-medium blueGray dark:text-white">{{ progressDict[topic] }}</span>
            <span class="text-sm font-medium blueGray dark:text-white">{{ progress }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div class="bg-peachPink-500 h-2.5 rounded-full" :style="'width: ' + progress + '%'"></div>
          </div>
        </div>
      </template>
    </div>
</template>

<script setup>

  import { watch } from 'vue';

  const props = defineProps({
    progress: {
      type: Object,
      required: true,
      default: () => ({
        'l': 0, // location
        's': 0, // surrounding
        't': 0, // type
        'f': 0, // furnishing
        'g': 0, // guest_expectations
      })
    }
  })

  const progressDict = {
    'l': 'Lage',
    's': 'Umgebung',
    't': 'Art der Unterkunft',
    'f': 'Einrichtung',
    'g': 'Gästeerwartungen',
  }

  // Watch progress and update
  watch(() => props.progress, (newVal, oldVal) => {
    // find the differences in newVal and oldVal
    const diff = Object.keys(newVal).reduce((acc, key) => {
      if (newVal[key] > oldVal[key]) {
        acc[key] = newVal[key]
      }
      return acc
    }, {})

    // animate the values in diff
    Object.keys(diff).forEach(key => {
      const start = oldVal[key]
      const end = newVal[key]
      const duration = 1000
      const range = end - start
      let current = start
      const increment = end > start ? 1 : -1
      const stepTime = Math.abs(Math.floor(duration / range))
      const timer = setInterval(() => {
        current += increment
        if (current === end) {
          clearInterval(timer)
        }
        props.progress[key] = current
      }, stepTime)
    })

  })

</script>

<style>
/* Your styles here */
</style>
