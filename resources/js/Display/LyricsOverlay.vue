<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue'
import { useGraphicsStore } from '@/Stores/graphics'

const store = useGraphicsStore()
const visible = computed(() => store.state.lyricsVisible)
const song = computed(() => store.state.activeSong)
const hasSlide = computed(() => store.state.activeSlide !== null && song.value?.slides[store.state.activeSlide!] != null)
const currentSlide = computed(() => {
  if (!song.value || store.state.activeSlide === null) return null
  return song.value.slides[store.state.activeSlide]
})

function handleKeydown(e: KeyboardEvent) {
  const tag = (e.target as HTMLElement)?.tagName
  if (tag === 'INPUT' || tag === 'TEXTAREA' || (e.target as HTMLElement)?.isContentEditable) return
  if (e.key === ' ' || e.code === 'Space') { e.preventDefault(); store.nextSlide() }
  if (e.key === 'ArrowLeft') { e.preventDefault(); store.prevSlide() }
  if (e.key === 'ArrowRight') { e.preventDefault(); store.nextSlide() }
}

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
</script>

<template>
  <Transition name="lt">
    <div v-if="visible && song" :class="['fixed bottom-[50px] left-[60px] flex rounded-xl overflow-hidden shadow-[0_8px_32px_rgba(0,0,0,0.35),0_2px_8px_rgba(0,0,0,0.2)] z-[90] min-w-[400px] bg-[rgba(10,15,25,0.88)] backdrop-blur-xl border-l-[5px] border-l-[#D4AF37]', hasSlide ? 'w-[92%]' : 'w-auto']">
      <div class="flex-[0_0_280px] gap-1 px-16 py-4 flex flex-col justify-center rounded-r-xl border-r-[#D4AF37] border-r-[5px] bg-inherit">
        <h1 class="text-[2vw] font-bold text-white whitespace-nowrap overflow-hidden text-ellipsis uppercase leading-none">{{ song.title }}</h1>
        <p v-if="song.artist" class="text-[1.7vw] font-medium text-[#D4AF37] whitespace-nowrap overflow-hidden text-ellipsis leading-none m-0">{{ song.artist }}</p>
      </div>
      <div v-if="hasSlide && currentSlide" class="flex-1 px-6 py-3 flex flex-col items-center justify-center">
        <p v-if="currentSlide.section_label" class="text-[1vw] font-semibold uppercase text-[#D4AF37] tracking-wider m-0 mb-1.5 leading-none">{{ currentSlide.section_label }}</p>
        <p class="text-[1.7vw] text-white m-0 leading-[1.4] break-words text-center">{{ currentSlide.content }}</p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.lt-enter-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.lt-leave-active { transition: all 0.3s ease-in; }
.lt-enter-from { transform: translateX(-120%); opacity: 0; }
.lt-leave-to { transform: translateX(-120%); opacity: 0; }
</style>
