<script setup lang="ts">
import { computed } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'

const store = useGraphicsStore()
const visible = computed(() => store.state.lyricsVisible)
const song = computed(() => store.state.activeSong)
const hasSlide = computed(() => store.state.activeSlide !== null && song.value?.slides[store.state.activeSlide!] != null)
const currentSlide = computed(() => {
  if (!song.value || store.state.activeSlide === null) return null
  return song.value.slides[store.state.activeSlide]
})
</script>

<template>
  <Transition name="lt">
    <div v-if="visible && song" :class="['lyrics-bar', hasSlide ? 'lyrics-bar--expanded' : 'lyrics-bar--compact']">
      <div class="lyrics-info">
        <h1 class="lyrics-title">{{ song.title }}</h1>
        <p v-if="song.artist" class="lyrics-artist">{{ song.artist }}</p>
      </div>
      <div v-if="hasSlide && currentSlide" class="lyrics-text">
        <p v-if="currentSlide.section_label" class="lyrics-section-label">{{ currentSlide.section_label }}</p>
        <p class="lyrics-content">{{ currentSlide.content }}</p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.lyrics-bar {
  position: fixed;
  bottom: 50px;
  left: 60px;
  display: flex;
  background: rgba(10, 15, 25, 0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-left: 5px solid #D4AF37;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0,0,0,0.35), 0 2px 8px rgba(0,0,0,0.2);
  z-index: 90;
  min-width: 400px;
}

.lyrics-bar--compact {
  width: auto;
}

.lyrics-bar--expanded {
  width: 92%;
}

.lyrics-info {
  flex: 0 0 280px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  border-right: 1px solid rgba(255,255,255,0.08);
}

.lyrics-title {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 36px;
  font-weight: 700;
  color: #fff;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.lyrics-artist {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 20px;
  font-weight: 500;
  color: #D4AF37;
  margin: 4px 0 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.lyrics-text {
  flex: 1;
  padding: 18px 24px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: center;
}

.lyrics-section-label {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #D4AF37;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0 0 6px 0;
  line-height: 1;
}

.lyrics-content {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  font-size: 32px;
  font-weight: 600;
  color: #fff;
  margin: 0;
  line-height: 1.4;
  overflow-wrap: break-word;
  word-break: break-word;
  text-align: right;
}

.lt-enter-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.lt-leave-active { transition: all 0.3s ease-in; }
.lt-enter-from { transform: translateX(-120%); opacity: 0; }
.lt-leave-to { transform: translateX(-120%); opacity: 0; }
</style>
