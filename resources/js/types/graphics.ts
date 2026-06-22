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
  slides: SongSlide[]
  created_at: string
  updated_at: string
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
