<template>
    <div class="flex flex-col items-center h-full w-full justify-center absolute top-0 left-0">
        <p class="mb-6 text-sm text-blueGray"><slot></slot></p>
    <div class="square-circle-8"></div>
    </div>
</template>

<script setup>
    import { useSlots, computed } from 'vue'
    const slots = useSlots()

    const isSlotEmpty = computed(() => {
        return !!slots['default'];
    })
</script>

<style scoped>
.square-circle-8 {
  width:75px;
  aspect-ratio:1;
  display:grid;
}
.square-circle-8:before,
.square-circle-8:after {
  content:"";
  grid-area:1/1;
  width:35px;
  aspect-ratio:1;
  box-shadow:0 0 0 3px #f3b191 inset;
  filter: drop-shadow(40px 40px 0 #f3b191);
  animation:sc8 2s infinite alternate;
}
.square-circle-8:after {
  margin:0 0 0 auto;
  filter: drop-shadow(-40px 40px 0 #f3b191);
  animation-delay:-1s;
}
@keyframes sc8 {
  0%,10%   {border-radius:0}
  30%,40%  {border-radius:50% 0}
  60%,70%  {border-radius:50%}
  90%,100% {border-radius:0 50%}
}
</style>
