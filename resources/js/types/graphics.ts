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

export function parseLyricsToSlides(lyrics: string | null): SongSlide[] {
  if (!lyrics) return []
  const lines = lyrics.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n')
  const slides: SongSlide[] = []
  let index = 0
  for (let i = 0; i < lines.length; i += 2) {
    let content = lines[i].trim()
    if (lines[i + 1] !== undefined) content += '\n' + lines[i + 1].trim()
    if (content.trim()) {
      slides.push({ id: 0, slide_order: index++, content })
    }
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
