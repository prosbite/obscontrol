<script setup lang="ts">
defineProps<{
  image: string | null
  width: string | null
}>()
</script>

<template>
  <div v-if="image" class="image-only-wrapper">
    <img :src="image" :style="{ width: width || '10vw', height: 'auto' }" class="image-only-img" alt="" />
  </div>
</template>

<style scoped>
.image-only-wrapper {
  display: flex;
}
.image-only-img {
  display: block;
  border-radius: 8px;
  max-width: 100%;
}
</style>
