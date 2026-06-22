<script setup lang="ts">
defineProps<{
  name: string
  subtitle: string | null
  image: string | null
}>()
</script>

<template>
  <div class="banner" :style="image ? { backgroundImage: `url(${image})` } : {}">
    <div class="banner-overlay">
      <h1 class="banner-name">{{ name }}</h1>
      <p v-if="subtitle" class="banner-subtitle">{{ subtitle }}</p>
    </div>
  </div>
</template>

<style scoped>
.banner {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-color: #0A1F3F;
}
.banner-overlay {
  background: linear-gradient(135deg, rgba(10, 31, 63, 0.92), rgba(10, 31, 63, 0.75));
  padding: 22px 36px;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}
.banner-name {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 40px;
  font-weight: 800;
  color: #fff;
  margin: 0;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.banner-subtitle {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 22px;
  font-weight: 500;
  color: #D4AF37;
  margin: 4px 0 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
