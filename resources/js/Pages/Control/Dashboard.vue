<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import echo from '@/echo'
import { useGraphicsStore } from '@/Stores/graphics'
import type { LowerThird, Song, Scripture, Announcement } from '@/types/graphics'
import { parseLyricsToSlides } from '@/types/graphics'
import axios from 'axios'
import Modal from '@/Components/sidebar/UI/Modal.vue'
import LowerThirdPreview from '@/Components/sidebar/LowerThirds/LowerThirdPreview.vue'

const designs = [
  { value: 'classic', label: 'Classic', desc: 'Logo panel + text section' },
  { value: 'minimal', label: 'Minimal', desc: 'Clean dark bar with accent border' },
  { value: 'banner', label: 'Banner', desc: 'Full-width with optional background' },
] as const

const store = useGraphicsStore()
const lowerThirds = ref<LowerThird[]>([])
const songs = ref<Song[]>([])
const scriptures = ref<Scripture[]>([])
const announcements = ref<Announcement[]>([])
const searchQuery = ref('')
const activeTab = ref<'lowerthirds' | 'lyrics' | 'scriptures' | 'timer' | 'announcements'>('lowerthirds')
const newTimerDuration = ref(600)
const selectedSong = ref<Song | null>(null)
const songModal = ref(false)
const editingSong = ref<Song | null>(null)
const songForm = ref<{ title: string; artist: string; lyrics: string }>({ title: '', artist: '', lyrics: '' })

const ltModal = ref(false)
const selectDesign = ref(false)
const ltForm = ref<{ id?: number; name: string; subtitle: string; image: string; template: string }>({ name: '', subtitle: '', image: '', template: 'classic' })
const isEditing = ref(false)

let channel: any = null

const filteredLowerThirds = computed(() =>
  lowerThirds.value.filter(lt => lt.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
)
const filteredSongs = computed(() =>
  songs.value.filter(s => s.title.toLowerCase().includes(searchQuery.value.toLowerCase()))
)

function pickDesign(template: string) {
  ltForm.value.template = template
  selectDesign.value = false
}

function openNewLt() {
  ltForm.value = { name: '', subtitle: '', image: '', template: 'classic' }
  isEditing.value = false
  selectDesign.value = true
  ltModal.value = true
}

function openEditLt(lt: LowerThird) {
  ltForm.value = { id: lt.id, name: lt.name, subtitle: lt.subtitle ?? '', image: lt.image ?? '', template: lt.template }
  isEditing.value = true
  selectDesign.value = false
  ltModal.value = true
}

async function saveLt() {
  try {
    const payload = {
      name: ltForm.value.name,
      subtitle: ltForm.value.subtitle || null,
      image: ltForm.value.image || null,
      template: ltForm.value.template,
    }
    if (isEditing.value && ltForm.value.id) {
      const res = await axios.put(`/api/lower-thirds/${ltForm.value.id}`, payload)
      const idx = lowerThirds.value.findIndex(lt => lt.id === ltForm.value.id)
      if (idx !== -1) lowerThirds.value[idx] = res.data.data
    } else {
      const res = await axios.post('/api/lower-thirds', payload)
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
async function goToSlide(index: number) {
  await axios.post('/api/control/lyrics/go-to', { slide_index: index })
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

function selectSong(song: Song) {
  selectedSong.value = song
}
function closeSongPreview() {
  selectedSong.value = null
}

function openNewSong() {
  editingSong.value = null
  songForm.value = { title: '', artist: '', lyrics: '' }
  songModal.value = true
}
function openEditSong(song: Song) {
  editingSong.value = song
  songForm.value = { title: song.title, artist: song.artist ?? '', lyrics: song.lyrics ?? '' }
  songModal.value = true
}
async function saveSong() {
  try {
    const payload = {
      title: songForm.value.title,
      artist: songForm.value.artist || null,
      lyrics: songForm.value.lyrics || null,
    }
    if (editingSong.value) {
      const { data } = await axios.put(`/api/songs/${editingSong.value.id}`, payload)
      const idx = songs.value.findIndex(s => s.id === editingSong.value!.id)
      if (idx !== -1) songs.value[idx] = data.data
    } else {
      const { data } = await axios.post('/api/songs', payload)
      songs.value.push(data.data)
    }
    songModal.value = false
    editingSong.value = null
  } catch (e: any) {
    alert(e?.response?.data?.message || e?.message || 'Failed to save song')
  }
}

async function deleteSong(id: number) {
  if (!confirm('Delete this song?')) return
  try {
    await axios.delete(`/api/songs/${id}`)
    songs.value = songs.value.filter(s => s.id !== id)
  } catch (e) {
    console.error('Failed to delete', e)
  }
}

function isInput(e: KeyboardEvent) {
  const tag = (e.target as HTMLElement)?.tagName
  return tag === 'INPUT' || tag === 'TEXTAREA' || (e.target as HTMLElement)?.isContentEditable
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === ' ' || e.code === 'Space') { if (isInput(e)) return; e.preventDefault(); nextSlide() }
  if (e.key === 'ArrowLeft') { if (isInput(e)) return; e.preventDefault(); prevSlide() }
  if (e.key === 'ArrowRight') { if (isInput(e)) return; e.preventDefault(); nextSlide() }
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
              <button @click="hideLowerThird()" class="w-full px-3 py-2 text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide Lower Third</button>
              <button @click="hideLyrics()" class="w-full px-3 py-2 text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide Lyrics</button>
              <button @click="hideScripture()" class="w-full px-3 py-2 text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide Scripture</button>
              <button @click="hideLowerThird(); hideLyrics(); hideScripture(); hideAnnouncement()" class="w-full px-3 py-2 text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">Hide All</button>
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
            <button v-if="activeTab === 'lyrics' && !selectedSong" @click="openNewSong" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition-colors">+ New Song</button>
          </div>

          <div v-if="activeTab === 'lowerthirds'" class="grid grid-cols-1 gap-3">
            <div v-for="lt in filteredLowerThirds" :key="lt.id" class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
              <div class="p-4 cursor-pointer" @click="openEditLt(lt)">
                <LowerThirdPreview :name="lt.name" :subtitle="lt.subtitle" :image="lt.image" :template="lt.template" />
                <div class="mt-3 flex items-center justify-between">
                  <div class="min-w-0 flex-1">
                    <h4 class="font-semibold text-white truncate">{{ lt.name }}</h4>
                    <p v-if="lt.subtitle" class="text-sm text-gray-400 truncate">{{ lt.subtitle }}</p>
                  </div>
                  <span class="text-xs text-gray-500 capitalize ml-2 shrink-0">{{ lt.template }}</span>
                </div>
              </div>
              <div class="px-4 pb-4 flex gap-2">
                <button @click="showLowerThird(lt.id)" class="flex-1 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">Show</button>
                <button @click="deleteLt(lt.id)" class="py-2 px-4 text-sm bg-red-600/20 hover:bg-red-600/40 text-red-400 rounded-lg transition-colors">Delete</button>
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'lyrics'" class="relative">
            <div v-if="!selectedSong" class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div v-for="song in filteredSongs" :key="song.id" class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
                <div class="p-4 cursor-pointer hover:bg-gray-800/50 transition-colors" @click="selectSong(song)">
                  <h4 class="font-semibold text-white">{{ song.title }}</h4>
                  <p v-if="song.artist" class="text-sm text-gray-400">{{ song.artist }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ song.slides?.length ?? 0 }} slides</p>
                </div>
                <div class="px-4 pb-4 flex gap-2">
                  <button @click="selectSong(song)" class="flex-1 py-2 text-sm bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-400 rounded-lg transition-colors">View</button>
                  <button @click="openEditSong(song)" class="py-2 px-4 text-sm bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg transition-colors">Edit</button>
                  <button @click="deleteSong(song.id)" class="py-2 px-4 text-sm bg-red-600/20 hover:bg-red-600/40 text-red-400 rounded-lg transition-colors">Del</button>
                </div>
              </div>
            </div>

            <div v-else class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden relative">
              <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <div>
                  <h3 class="text-lg font-bold text-white">{{ selectedSong.title }}</h3>
                  <p v-if="selectedSong.artist" class="text-sm text-gray-400">{{ selectedSong.artist }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <button @click.prevent="showSong(selectedSong.id)" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">Send to Display</button>
                  <button @click="closeSongPreview" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white transition-colors text-lg font-bold">&times;</button>
                </div>
              </div>
              <div class="p-6 max-h-[500px] overflow-y-auto space-y-4">
                <div v-for="(slide, i) in selectedSong.slides" :key="slide.id" @click="goToSlide(i)" :class="['rounded-lg p-4 border transition-all cursor-pointer', store.state.lyricsVisible && store.state.activeSong?.id === selectedSong.id && store.state.activeSlide === i ? 'bg-indigo-900/40 border-indigo-500' : 'bg-gray-800 border-transparent hover:border-gray-600']">
                  <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">Slide {{ i + 1 }} / {{ selectedSong.slides.length }}</p>
                    <span v-if="store.state.lyricsVisible && store.state.activeSong?.id === selectedSong.id && store.state.activeSlide === i" class="text-xs text-indigo-400 font-medium">&#9679; Current</span>
                  </div>
                  <p class="text-white text-base leading-relaxed whitespace-pre-wrap">{{ slide.content }}</p>
                </div>
              </div>
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

      <!-- Step 1: Pick a design -->
      <div v-if="selectDesign && !isEditing" class="space-y-4">
        <p class="text-sm text-gray-400">Choose a lower third design:</p>
        <div class="grid grid-cols-1 gap-3">
          <div v-for="d in designs" :key="d.value"
            @click="pickDesign(d.value)"
            class="bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-indigo-500 rounded-xl p-4 cursor-pointer transition-all">
            <LowerThirdPreview :name="'John Doe'" :subtitle="'SPEAKER / GUEST'" :template="d.value" />
            <h4 class="font-semibold text-white mt-3 capitalize">{{ d.label }}</h4>
            <p class="text-xs text-gray-400 mt-0.5">{{ d.desc }}</p>
          </div>
        </div>
        <div class="flex justify-end pt-2">
          <button type="button" @click="ltModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">Cancel</button>
        </div>
      </div>

      <!-- Step 2: Fill in properties -->
      <form v-else @submit.prevent="saveLt" class="space-y-4">
        <div v-if="!isEditing" class="flex gap-2 items-center text-sm text-gray-400 mb-2">
          <span>Design:</span>
          <span class="text-white capitalize font-medium">{{ ltForm.template }}</span>
          <button type="button" @click="selectDesign = true" class="text-indigo-400 hover:text-indigo-300 underline ml-1">Change</button>
        </div>

        <div>
          <label class="block text-sm text-gray-400 mb-1">Name <span class="text-red-400">*</span></label>
          <input v-model="ltForm.name" required class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="e.g. John Doe" />
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Subtitle</label>
          <input v-model="ltForm.subtitle" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="e.g. Speaker / Guest" />
        </div>

        <div v-if="ltForm.template === 'banner'">
          <label class="block text-sm text-gray-400 mb-1">Background Image URL</label>
          <input v-model="ltForm.image" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="https://example.com/bg.jpg" />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="ltModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition-colors">{{ isEditing ? 'Update' : 'Create' }}</button>
        </div>
      </form>
    </Modal>

    <Modal :show="songModal" :title="editingSong ? 'Edit Song' : 'New Song'" @close="songModal = false; editingSong = null; songForm = { title: '', artist: '', lyrics: '' }">
      <form @submit.prevent="saveSong" class="space-y-4">
        <div>
          <label class="block text-sm text-gray-400 mb-1">Title <span class="text-red-400">*</span></label>
          <input v-model="songForm.title" required class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500" />
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Artist</label>
          <input v-model="songForm.artist" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500" />
        </div>
        <div>
          <label class="block text-sm text-gray-400 mb-1">Lyrics <span class="text-gray-500">(blank lines separate slides, 2 lines per slide)</span></label>
          <textarea v-model="songForm.lyrics" rows="12" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500 resize-none font-mono" placeholder="Enter full lyrics here.&#10;&#10;Each pair of lines becomes one slide."></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="songModal = false; editingSong = null; songForm = { title: '', artist: '', lyrics: '' }" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition-colors">{{ editingSong ? 'Update' : 'Create' }}</button>
        </div>
      </form>
    </Modal>
  </AuthenticatedLayout>
</template>
