export interface LowerThird {
  id: number
  name: string
  subtitle: string | null
  image: string | null
  template: string
  created_at: string
  updated_at: string
}

export interface SongSlide {
  id: number
  slide_order: number
  content: string
  section_label?: string | null
}

export interface Song {
  id: number
  title: string
  artist: string | null
  category: string | null
  lyrics: string | null
  slides: SongSlide[]
  created_at: string
  updated_at: string
}

const SECTION_HEADER_RE = /^(verse|refrain|chorus|coda|bridge|intro|outro|pre-chorus|interlude|ending|tag|solo)\s*\d*$/i

export function parseLyricsToSlides(lyrics: string | null): SongSlide[] {
  if (!lyrics) return []
  const lines = lyrics.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n')
  const buffer: { text: string; section: string | null }[] = []
  let currentSection: string | null = null
  for (const line of lines) {
    const trimmed = line.trim()
    if (!trimmed) continue
    if (SECTION_HEADER_RE.test(trimmed)) {
      currentSection = trimmed
      continue
    }
    buffer.push({ text: trimmed, section: currentSection })
  }
  const slides: SongSlide[] = []
  let index = 0
  for (let i = 0; i < buffer.length; i += 2) {
    let content = buffer[i].text
    const section = buffer[i].section
    if (buffer[i + 1] !== undefined) content += '\n' + buffer[i + 1].text
    const slide: SongSlide = { id: 0, slide_order: index++, content }
    if (section) slide.section_label = section
    slides.push(slide)
  }
  return slides
}

export interface Scripture {
  id: number
  reference: string
  text: string
  translation: string | null
  created_at: string
  updated_at: string
}

export interface Announcement {
  id: number
  title: string
  content: string
  image: string | null
  created_at: string
  updated_at: string
}

export interface QueueItemResource {
  id: string
  name: string
  type: 'lowerthird' | 'lyrics'
  source_id: number
  position: number
  created_at: string
}

export interface QueueSet {
  id: number
  name: string
  items?: QueueItemResource[]
  created_at: string
  updated_at: string
}

export interface GraphicsState {
  activeLowerThird: LowerThird | null
  activeSong: Song | null
  activeSlide: number | null
  lyricsVisible: boolean
  lowerThirdVisible: boolean
  activeScripture: Scripture | null
  scriptureVisible: boolean
  timerRunning: boolean
  timerPaused: boolean
  timerDuration: number
  timerRemaining: number
  timerStartedAt: number | null
  activeAnnouncement: Announcement | null
  announcementVisible: boolean
  announcements: Announcement[]
  announcementIndex: number
}