<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import echo from '@/echo'
import { useGraphicsStore } from '@/Stores/graphics'
import type { LowerThird, Song, Scripture, Announcement } from '@/types/graphics'
import axios from 'axios'
import Modal from '@/Components/sidebar/UI/Modal.vue'

const store = useGraphicsStore()
const lowerThirds = ref<LowerThird[]>([])
const songs = ref<Song[]>([])
const scriptures = ref<Scripture[]>([])
const announcements = ref<Announcement[]>([])
const searchQuery = ref('')
const activeTab = ref<'lowerthirds' | 'lyrics' | 'scriptures' | 'timer' | 'announcements'>('lowerthirds')
const newTimerDuration = ref(600)

const ltModal = ref(false)
const ltForm = ref<{ id?: number; name: string; subtitle: string; template: string }>({ name: '', subtitle: '', template: 'slide_left' })
const isEditing = ref(false)

let channel: any = null

const filteredLowerThirds = computed(() =>
  lowerThirds.value.filter(lt => lt.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
)
const filteredSongs = computed(() =>
  songs.value.filter(s => s.title.toLowerCase().includes(searchQuery.value.toLowerCase()))
)

function openNewLt() {
  ltForm.value = { name: '', subtitle: '', template: 'slide_left' }
  isEditing.value = false
  ltModal.value = true
}

function openEditLt(lt: LowerThird) {
  ltForm.value = { id: lt.id, name: lt.name, subtitle: lt.subtitle ?? '', template: lt.template }
  isEditing.value = true
  ltModal.value = true
}

async function saveLt() {
  try {
    if (isEditing.value && ltForm.value.id) {
      const res = await axios.put(`/api/lower-thirds/${ltForm.value.id}`, {
        name: ltForm.value.name,
        subtitle: ltForm.value.subtitle || null,
        template: ltForm.value.template,
      })
      const idx = lowerThirds.value.findIndex(lt => lt.id === ltForm.value.id)
      if (idx !== -1) lowerThirds.value[idx] = res.data.data
    } else {
      const res = await axios.post('/api/lower-thirds', {
        name: ltForm.value.name,
        subtitle: ltForm.value.subtitle || null,
        template: ltForm.value.template,
      })
      lowerThirds.value.push(res.data.data)
    }
    ltModal.value = false
  } catch (e) {
    console.error('Failed to save lower third', e)
  }
}

async function deleteLt(id: number) {
  if (!confirm('Delete this lower third?')) return
  try {
    await axios.delete(`/api/lower-thirds/${id}`)
    lowerThirds.value = lowerThirds.value.filter(lt => lt.id !== id)
  } catch (e) {
    console.error('Failed to delete', e)
  }
}

async function fetchAll() {
  try {
    const [ltRes, songRes, scRes, annRes] = await Promise.all([
      axios.get('/api/lower-thirds'),
      axios.get('/api/songs'),
      axios.get('/api/scriptures'),
      axios.get('/api/announcements'),
    ])
    lowerThirds.value = ltRes.data.data
    songs.value = songRes.data.data
    scriptures.value = scRes.data.data
    announcements.value = annRes.data.data
  } catch (e) {
    console.error('Failed to fetch data', e)
  }
}

async function showLowerThird(id: number) {
  await axios.post('/api/control/lowerthird/show', { id })
}
async function hideLowerThird() {
  await axios.post('/api/control/lowerthird/hide')
}
async function showSong(song_id: number) {
  await axios.post('/api/control/lyrics/show', { song_id })
}
async function hideLyrics() {
  await axios.post('/api/control/lyrics/hide')
}
async function nextSlide() {
  await axios.post('/api/control/lyrics/next')
}
async function prevSlide() {
  await axios.post('/api/control/lyrics/previous')
}
async function showScripture(id: number) {
  await axios.post('/api/control/scripture/show', { id })
}
async function hideScripture() {
  await axios.post('/api/control/scripture/hide')
}
async function startTimer() {
  await axios.post('/api/control/timer/start', { duration: newTimerDuration.value })
}
async function pauseTimer() {
  await axios.post('/api/control/timer/pause')
}
async function resetTimer() {
  await axios.post('/api/control/timer/reset')
}
async function stopTimer() {
  await axios.post('/api/control/timer/stop')
}
async function showAnnouncement(id: number) {
  await axios.post('/api/control/announcement/show', { id })
}
async function hideAnnouncement() {
  await axios.post('/api/control/announcement/hide')
}
async function nextAnnouncement() {
  await axios.post('/api/control/announcement/next')
}
async function prevAnnouncement() {
  await axios.post('/api/control/announcement/previous')
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === ' ' || e.code === 'Space') { e.preventDefault(); nextSlide() }
  if (e.key === 'ArrowLeft') { e.preventDefault(); prevSlide() }
  if (e.key === 'l' || e.key === 'L') { store.state.lyricsVisible ? hideLyrics() : showSong(store.state.activeSong?.id ?? 0) }
  if (e.key === 'Escape') { hideLyrics(); hideLowerThird(); hideScripture(); hideAnnouncement() }
}

onMounted(async () => {
  await fetchAll()

  channel = echo.channel('graphics')
  channel.listen('.LowerThirdShown', (e: any) => store.sync({ activeLowerThird: e.lowerThird, lowerThirdVisible: true }))
  channel.listen('.LowerThirdHidden', () => store.sync({ lowerThirdVisible: false }))
  channel.listen('.LyricsShown', (e: any) => store.sync({ activeSong: e.song, activeSlide: 0, lyricsVisible: true }))
  channel.listen('.LyricsHidden', () => store.sync({ lyricsVisible: false }))
  channel.listen('.LyricsSlideChanged', (e: any) => store.sync({ activeSlide: e.slideIndex }))
  channel.listen('.ScriptureShown', (e: any) => store.sync({ activeScripture: e.scripture, scriptureVisible: true }))
  channel.listen('.ScriptureHidden', () => store.sync({ scriptureVisible: false }))
  channel.listen('.TimerStarted', (e: any) => store.sync({ timerRunning: true, timerPaused: false, timerDuration: e.duration, timerRemaining: e.duration, timerStartedAt: Date.now() / 1000 }))
  channel.listen('.TimerPaused', (e: any) => store.sync({ timerRunning: false, timerPaused: true, timerRemaining: e.remaining }))
  channel.listen('.TimerStopped', () => store.sync({ timerRunning: false, timerPaused: false }))
  channel.listen('.AnnouncementShown', (e: any) => store.sync({ activeAnnouncement: e.announcement, announcementVisible: true }))
  channel.listen('.AnnouncementHidden', () => store.sync({ announcementVisible: false }))

  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  channel?.leave('graphics')
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Head title="Graphics Control" />
  <AuthenticatedLayout>
    <div class="p-6">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-4">
          <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Active Overlays</h3>
            <div class="space-y-2">
              <div class="flex items-center gap-2 text-sm">
                <span :class="['w-2 h-2 rounded-full', store.state.lowerThirdVisible ? 'bg-green-500' : 'bg-gray-600']"></span>
                <span class="text-gray-300">Lower Third</span>
                <span v-if="store.state.activeLowerThird" class="text-gray-500 truncate flex-1 text-right">{{ store.state.activeLowerThird.name }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span :class="['w-2 h-2 rounded-full', store.state.lyricsVisible ? 'bg-green-500' : 'bg-gray-600']"></span>
                <span class="text-gray-300">Lyrics</span>
                <span v-if="store.state.activeSong" class="text-gray-500 truncate flex-1 text-right">{{ store.state.activeSong.title }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span :class="['w-2 h-2 rounded-full', store.state.scriptureVisible ? 'bg-green-500' : 'bg-gray-600']"></span>
                <span class="text-gray-300">Scripture</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span :class="['w-2 h-2 rounded-full', store.state.timerRunning || store.state.timerPaused ? 'bg-green-500' : 'bg-gray-600']"></span>
                <span class="text-gray-300">Timer</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span :class="['w-2 h-2 rounded-full', store.state.announcementVisible ? 'bg-green-500' : 'bg-gray-600']"></span>
                <span class="text-gray-300">Announcement</span>
              </div>
            </div>
          </div>

          <div class="bg-gray-900 rounded-xl p-4 border border-gray-800">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Quick Actions</h3>
            <div class="space-y-2">
              <button @click="hideLowerThird()" class="w-full px-3 py-2 text-sm bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide Lower Third</button>
              <button @click="hideLyrics()" class="w-full px-3 py-2 text-sm bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide Lyrics</button>
              <button @click="hideScripture()" class="w-full px-3 py-2 text-sm bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide Scripture</button>
              <button @click="hideAnnouncement()" class="w-full px-3 py-2 text-sm bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide All</button>
            </div>
          </div>
        </div>

        <div class="lg:col-span-3 space-y-4">
          <div class="flex gap-2 border-b border-gray-800 pb-2">
            <button v-for="tab in ['lowerthirds', 'lyrics', 'scriptures', 'timer', 'announcements']" :key="tab" @click="activeTab = tab" :class="['px-4 py-2 text-sm rounded-t-lg transition-colors', activeTab === tab ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-gray-200']">
              {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
            </button>
          </div>

          <div class="mb-4 flex gap-2">
            <input v-model="searchQuery" placeholder="Search..." class="flex-1 px-4 py-2 bg-gray-900 border border-gray-800 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500" />
            <button v-if="activeTab === 'lowerthirds'" @click="openNewLt" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition-colors">+ New</button>
          </div>

          <div v-if="activeTab === 'lowerthirds'" class="grid grid-cols-1 gap-3">
            <div v-for="lt in filteredLowerThirds" :key="lt.id" class="bg-gray-900 rounded-xl p-4 border border-gray-800 flex items-center justify-between">
              <div class="flex-1 min-w-0 cursor-pointer" @click="openEditLt(lt)">
                <h4 class="font-semibold text-white truncate">{{ lt.name }}</h4>
                <p v-if="lt.subtitle" class="text-sm text-gray-400 truncate">{{ lt.subtitle }}</p>
              </div>
              <div class="flex items-center gap-2 shrink-0 ml-4">
                <button @click="showLowerThird(lt.id)" class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">Show</button>
                <button @click="deleteLt(lt.id)" class="px-3 py-1.5 text-xs bg-red-600/20 hover:bg-red-600/40 text-red-400 rounded-lg transition-colors">Del</button>
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'lyrics'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div v-for="song in filteredSongs" :key="song.id" class="bg-gray-900 rounded-xl p-4 border border-gray-800 cursor-pointer" @click="showSong(song.id)">
              <h4 class="font-semibold text-white">{{ song.title }}</h4>
              <p v-if="song.artist" class="text-sm text-gray-400">{{ song.artist }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ song.slides?.length ?? 0 }} slides</p>
            </div>
          </div>

          <div v-if="activeTab === 'scriptures'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div v-for="sc in scriptures" :key="sc.id" class="bg-gray-900 rounded-xl p-4 border border-gray-800 cursor-pointer" @click="showScripture(sc.id)">
              <h4 class="font-semibold text-white">{{ sc.reference }}</h4>
              <p class="text-sm text-gray-400 line-clamp-2">{{ sc.text }}</p>
            </div>
          </div>

          <div v-if="activeTab === 'timer'" class="bg-gray-900 rounded-xl p-6 border border-gray-800">
            <h3 class="text-lg font-semibold text-white mb-4">Countdown Timer</h3>
            <div class="flex gap-2 mb-4">
              <button @click="newTimerDuration = 600" :class="['px-4 py-2 rounded-lg text-sm transition-colors', newTimerDuration === 600 ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700']">10 min</button>
              <button @click="newTimerDuration = 900" :class="['px-4 py-2 rounded-lg text-sm transition-colors', newTimerDuration === 900 ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700']">15 min</button>
              <button @click="newTimerDuration = 1800" :class="['px-4 py-2 rounded-lg text-sm transition-colors', newTimerDuration === 1800 ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700']">30 min</button>
            </div>
            <div class="flex gap-2">
              <button @click="startTimer" :disabled="store.state.timerRunning" class="px-6 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-lg text-sm transition-colors">Start</button>
              <button @click="pauseTimer" :disabled="!store.state.timerRunning" class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 text-white rounded-lg text-sm transition-colors">Pause</button>
              <button @click="resetTimer" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition-colors">Reset</button>
              <button @click="stopTimer" :disabled="!store.state.timerRunning && !store.state.timerPaused" class="px-6 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-lg text-sm transition-colors">Stop</button>
            </div>
          </div>

          <div v-if="activeTab === 'announcements'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div v-for="ann in announcements" :key="ann.id" class="bg-gray-900 rounded-xl p-4 border border-gray-800 cursor-pointer" @click="showAnnouncement(ann.id)">
              <h4 class="font-semibold text-white">{{ ann.title }}</h4>
              <p class="text-sm text-gray-400 line-clamp-2">{{ ann.content }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="ltModal" :title="isEditing ? 'Edit Lower Third' : 'New Lower Third'" @close="ltModal = false">
      <form @submit.prevent="saveLt" class="space-y-4">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Name</label>
          <input v-model="ltForm.name" required class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500" />
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Subtitle</label>
          <input v-model="ltForm.subtitle" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500" />
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Animation</label>
          <select v-model="ltForm.template" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
            <option value="slide_left">Slide Left</option>
            <option value="slide_right">Slide Right</option>
            <option value="fade">Fade</option>
            <option value="zoom">Zoom</option>
          </select>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="ltModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition-colors">{{ isEditing ? 'Update' : 'Create' }}</button>
        </div>
      </form>
    </Modal>
  </AuthenticatedLayout>
</template>
