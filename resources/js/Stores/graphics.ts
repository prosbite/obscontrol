import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { GraphicsState } from '@/types/graphics'

export const useGraphicsStore = defineStore('graphics', () => {
  const state = ref<GraphicsState>({
    activeLowerThird: null,
    activeSong: null,
    activeSlide: null,
    lyricsVisible: false,
    lowerThirdVisible: false,
    activeScripture: null,
    scriptureVisible: false,
    timerRunning: false,
    timerPaused: false,
    timerDuration: 600,
    timerRemaining: 600,
    timerStartedAt: null,
    activeAnnouncement: null,
    announcementVisible: false,
    announcements: [],
    announcementIndex: 0,
  })

  function sync(payload: Partial<GraphicsState>) {
    state.value = { ...state.value, ...payload }
  }

  return { state, sync }
})
