<script setup>
  import { onMounted } from 'vue';
  import { ref } from 'vue';
  import AgentListItem from './AgentListItem.vue';
  import { usePage } from '@inertiajs/vue3';

  const agents = ref([]);
  const loading = ref(true);
  const page = usePage()

  onMounted (async () => {

  });
</script>

<template>
  <div>
    <div>
      <ul>
        <li v-for="(agent, index) in page.props.agents" :key="index" class="mb-2">
          <AgentListItem :agent="agent" />
        </li>
      </ul>
    </div>
  </div>
</template>
