import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { GraphicsState } from '@/types/graphics'
import axios from 'axios'

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

  const currentSlide = computed(() => {
    if (!state.value.activeSong || state.value.activeSlide === null) return null
    return state.value.activeSong.slides[state.value.activeSlide] ?? null
  })

  function isActiveSlide(songId: number, slideIndex: number): boolean {
    return (
      state.value.lyricsVisible &&
      state.value.activeSong?.id === songId &&
      state.value.activeSlide === slideIndex
    )
  }

  async function showSong(songId: number) {
    const { data } = await axios.post('/api/control/lyrics/show', { song_id: songId })
    sync(data)
  }

  async function hideLyrics() {
    const { data } = await axios.post('/api/control/lyrics/hide')
    sync(data)
  }

  function goToSlide(index: number) {
    state.value.activeSlide = index
    axios.post('/api/control/lyrics/go-to', { slide_index: index })
  }

  function nextSlide() {
    if (state.value.activeSong?.slides.length) {
      const current = state.value.activeSlide
      const max = state.value.activeSong.slides.length - 1
      state.value.activeSlide = current === null ? 0 : Math.min(current + 1, max)
    }
    axios.post('/api/control/lyrics/next')
  }

  function prevSlide() {
    if (state.value.activeSong?.slides.length) {
      const current = state.value.activeSlide
      state.value.activeSlide = current === null ? 0 : Math.max(0, current - 1)
    }
    axios.post('/api/control/lyrics/previous')
  }

  return {
    state,
    sync,
    currentSlide,
    isActiveSlide,
    showSong,
    hideLyrics,
    goToSlide,
    nextSlide,
    prevSlide,
  }
})
