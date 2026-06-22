<script setup lang="ts">
import { computed } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'

const store = useGraphicsStore()
const visible = computed(() => store.state.lyricsVisible)
const currentSlide = computed(() => {
  if (!store.state.activeSong || store.state.activeSlide === null) return null
  return store.state.activeSong.slides[store.state.activeSlide]
})
</script>

<template>
  <Transition name="lyrics">
    <div v-if="visible && currentSlide" class="lyrics-overlay">
      <div class="lyrics-content">
        <p class="lyrics-text">{{ currentSlide.content }}</p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.lyrics-overlay {
  position: absolute;
  bottom: 180px;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
  color: white;
  text-shadow: 0 2px 8px rgba(0,0,0,0.6);
  max-width: 1400px;
  width: 90%;
}
.lyrics-text {
  font-size: 42px;
  font-weight: 600;
  line-height: 1.4;
  margin: 0;
}

.lyrics-enter-active { transition: all 0.4s ease-out; }
.lyrics-leave-active { transition: all 0.25s ease-in; }
.lyrics-enter-from { opacity: 0; transform: translateX(-50%) translateY(20px); }
.lyrics-leave-to { opacity: 0; transform: translateX(-50%) translateY(-20px); }
</style>
