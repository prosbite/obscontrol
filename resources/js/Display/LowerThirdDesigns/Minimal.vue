<script setup lang="ts">
defineProps<{
  name: string
  subtitle: string | null
}>()
</script>

<template>
  <div class="minimal-bar">
    <h1 class="minimal-name">{{ name }}</h1>
    <p v-if="subtitle" class="minimal-subtitle">{{ subtitle }}</p>
  </div>
</template>

<style scoped>
.minimal-bar {
  padding: 18px 28px;
}
.minimal-name {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 2vw;
  font-weight: 700;
  color: #fff;
  margin: 0;
  line-height: 1.2;
  white-space: nowrap;
  display: inline-block;
}
.minimal-subtitle {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 1.5vw;
  font-weight: 500;
  color: #D4AF37;
  margin: 4px 0 0;
  white-space: nowrap;
}
</style>
