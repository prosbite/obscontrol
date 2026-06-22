<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import echo from '@/echo'
import { useGraphicsStore } from '@/Stores/graphics'
import LowerThirdOverlay from '@/Display/LowerThirdOverlay.vue'
import LyricsOverlay from '@/Display/LyricsOverlay.vue'
import ScriptureOverlay from '@/Display/ScriptureOverlay.vue'
import CountdownTimer from '@/Display/CountdownTimer.vue'
import AnnouncementOverlay from '@/Display/AnnouncementOverlay.vue'

const store = useGraphicsStore()
let channel: any = null
let styleEl: HTMLStyleElement | null = null

onMounted(async () => {
  styleEl = document.createElement('style')
  styleEl.textContent = `html,body{margin:0!important;padding:0!important;background:transparent!important;overflow:hidden!important;width:1920px;height:1080px}`
  document.head.appendChild(styleEl)

  const res = await fetch('/api/control/state')
  if (res.ok) {
    const data = await res.json()
    store.sync(data)
  }

  channel = echo.channel('graphics')
  channel.listen('.LowerThirdShown', (e: any) => store.sync({ activeLowerThird: e.lowerThird, lowerThirdVisible: true }))
  channel.listen('.LowerThirdHidden', () => store.sync({ lowerThirdVisible: false }))
  channel.listen('.LyricsShown', (e: any) => store.sync({ activeSong: e.song, activeSlide: 0, lyricsVisible: true }))
  channel.listen('.LyricsHidden', () => store.sync({ lyricsVisible: false }))
  channel.listen('.LyricsSlideChanged', (e: any) => {
    store.sync({ activeSlide: e.slideIndex })
    if (store.state.activeSong) {
      store.state.activeSong = { ...store.state.activeSong }
    }
  })
  channel.listen('.ScriptureShown', (e: any) => store.sync({ activeScripture: e.scripture, scriptureVisible: true }))
  channel.listen('.ScriptureHidden', () => store.sync({ scriptureVisible: false }))
  channel.listen('.TimerStarted', (e: any) => store.sync({ timerRunning: true, timerPaused: false, timerDuration: e.duration, timerRemaining: e.duration, timerStartedAt: Date.now() / 1000 }))
  channel.listen('.TimerPaused', (e: any) => store.sync({ timerRunning: false, timerPaused: true, timerRemaining: e.remaining }))
  channel.listen('.TimerStopped', () => store.sync({ timerRunning: false, timerPaused: false }))
  channel.listen('.AnnouncementShown', (e: any) => store.sync({ activeAnnouncement: e.announcement, announcementVisible: true }))
  channel.listen('.AnnouncementHidden', () => store.sync({ announcementVisible: false }))
})

onUnmounted(() => {
  channel?.leave('graphics')
  styleEl?.remove()
})
</script>

<template>
  <Head title="Display" />

  <div class="display-container">
    <LowerThirdOverlay />
    <LyricsOverlay />
    <ScriptureOverlay />
    <CountdownTimer />
    <AnnouncementOverlay />
  </div>
</template>

<style>
.display-container {
  width: 1920px;
  height: 1080px;
  position: relative;
  background: transparent;
  overflow: hidden;
}
</style>
