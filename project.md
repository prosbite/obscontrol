# Church Livestream Graphics Controller

## Software Requirements Specification (SRS)

### Project Name

GraceStream Graphics Controller (working title)

### Overview

Develop a web-based graphics control system for OBS Studio that allows operators to remotely manage lower thirds, lyrics, scripture overlays, countdown timers, and future graphics modules through a browser.

The system must support real-time communication between controllers and display clients using WebSockets.

The system will consist of three major parts:

1. Backend Server (Laravel)
2. Controller Interface (Vue)
3. Display Client (Vue running inside OBS Browser Source)

The display client should function as a graphics engine and render overlays with transparent backgrounds directly inside OBS.

---

# Goals

Create a lightweight alternative to:

* ProPresenter
* EasyWorship
* OpenLP
* vMix Titles

Designed specifically for:

* Church services
* Evangelistic meetings
* Online worship services
* Livestream productions

---

# System Architecture

```text
Controller Device
(Phone, Tablet, Laptop)

        │
        │ WebSocket + API
        ▼

Laravel Backend
API + Reverb

        │
        │ Broadcast Events
        ▼

Display Client
(Vue App inside OBS Browser Source)

        │
        ▼

OBS Studio
```

---

# Technical Stack

## Backend

Laravel 13

Features:

* REST API
* Authentication
* WebSocket Broadcasting
* State Management

Packages:

* Laravel Reverb
* Laravel Sanctum

---

## Frontend

Vue 3
TypeScript

Packages:

* Pinia
* Vue Router
* Axios
* Laravel Echo

Styling:

* TailwindCSS

---

# Realtime Requirements

Use Laravel Reverb.

Every state change must be broadcast immediately.

Examples:

```json
{
  "event": "lowerthird.show"
}
```

```json
{
  "event": "lyrics.next"
}
```

```json
{
  "event": "overlay.hide"
}
```

The display client should never require refreshes.

---

# Core Concept

The server maintains a single source of truth.

All displays connect and synchronize automatically.

The server stores:

```json
{
  "activeLowerThird": null,
  "activeSong": null,
  "activeSlide": null,
  "lyricsVisible": false,
  "lowerThirdVisible": false
}
```

Whenever a controller sends a command:

1. Server updates state.
2. Server broadcasts update.
3. All display clients update instantly.

---

# User Roles

## Administrator

Can:

* Manage users
* Manage songs
* Manage lower thirds
* Manage templates
* View logs
* Configure system settings

---

## Operator

Can:

* Control graphics
* Show/hide overlays
* Change slides
* Operate livestream

---

# Module 1: Lower Thirds

## Purpose

Display speaker information.

Example:

```text
Pastor John Doe
Guest Speaker
```

---

## Database

Table: lower_thirds

Fields:

```text
id
name
subtitle
image
template
created_at
updated_at
```

---

## Features

Create lower third.

Edit lower third.

Delete lower third.

Preview lower third.

Show lower third.

Hide lower third.

Duplicate lower third.

Search lower thirds.

---

## Controller UI

```text
--------------------------------
LOWER THIRDS
--------------------------------

Search

Pastor John Doe
Elder Smith
Special Music

[SHOW]
[HIDE]
```

---

## Display Animations

Required:

* Slide Left
* Slide Right
* Fade
* Zoom

Configurable per template.

---

# Module 2: Lyrics

## Purpose

Display song lyrics during worship.

---

## Database

### songs

```text
id
title
artist
category
created_at
updated_at
```

### song_slides

```text
id
song_id
slide_order
content
```

---

## Example

Song:

Amazing Grace

Slides:

Slide 1

```text
Amazing grace
How sweet the sound
```

Slide 2

```text
I once was lost
But now am found
```

---

## Features

Create song.

Edit song.

Delete song.

Search songs.

Duplicate song.

Import lyrics.

---

## Controller UI

```text
Song List

Amazing Grace
How Great Thou Art
I Surrender All

--------------------------------

Slides

Verse 1
Verse 2
Chorus

Prev
Next

Show
Hide
```

---

## Keyboard Shortcuts

Required:

```text
Space = Next Slide

Left Arrow = Previous

L = Toggle Lyrics

Esc = Hide
```

---

# Module 3: Scripture Overlay

## Purpose

Display Bible verses.

---

## Database

### scriptures

```text
id
reference
text
translation
```

---

## Controller

```text
John 3:16

Show
Hide
```

---

# Module 4: Countdown Timer

## Purpose

Display countdown before stream starts.

---

## Features

Set duration.

Examples:

```text
10 minutes
15 minutes
30 minutes
```

Controls:

```text
Start
Pause
Reset
Stop
```

---

## Display

Large centered timer.

Example:

```text
09:58
```

---

# Module 5: Announcement Slides

## Purpose

Display announcements.

---

## Database

announcements

```text
id
title
content
image
```

---

## Features

Show announcement.

Hide announcement.

Next announcement.

Previous announcement.

---

# Display Client

## Route

```text
/display/main
```

Primary OBS Browser Source.

---

## Display Responsibilities

Render:

* Lower thirds
* Lyrics
* Scriptures
* Countdown
* Announcements

Transparent background.

No visible UI controls.

No scrollbars.

No browser chrome.

---

# OBS Integration

Browser Source URL:

```text
https://yourdomain.com/display/main
```

Recommended OBS Settings:

Width:
1920

Height:
1080

FPS:
60

Shutdown source when not visible:
Disabled

Refresh browser when scene becomes active:
Disabled

---

# Controller Application

## Route

```text
/control
```

Responsive.

Must work on:

* Desktop
* Tablet
* Mobile

---

## Dashboard Layout

```text
--------------------------------
ACTIVE OVERLAYS
--------------------------------

Lower Third
Lyrics
Scripture
Timer

--------------------------------
QUEUE
--------------------------------

Upcoming Items

--------------------------------
CONTROLS
--------------------------------
```

---

# Queue System

Operators can prepare overlays.

Example:

```text
1. Welcome
2. Speaker Intro
3. Special Music
4. Closing Prayer
```

Controls:

```text
Next
Previous
Activate
```

---

# State Synchronization

Every client joining must receive current state.

Example:

Operator refreshes page.

Display refreshes page.

System must immediately restore:

```json
{
  "activeSong": 5,
  "activeSlide": 2,
  "lyricsVisible": true
}
```

No manual recovery required.

---

# API Requirements

## Lower Third

```http
GET /api/lower-thirds

POST /api/lower-thirds

PUT /api/lower-thirds/{id}

DELETE /api/lower-thirds/{id}
```

---

## Lyrics

```http
GET /api/songs

POST /api/songs

PUT /api/songs/{id}

DELETE /api/songs/{id}
```

---

## Graphics Control

```http
POST /api/control/lowerthird/show

POST /api/control/lowerthird/hide

POST /api/control/lyrics/show

POST /api/control/lyrics/hide

POST /api/control/lyrics/next

POST /api/control/lyrics/previous
```

---

# WebSocket Events

## Broadcast Events

```text
LowerThirdShown
LowerThirdHidden

LyricsShown
LyricsHidden

LyricsSlideChanged

ScriptureShown
ScriptureHidden

TimerStarted
TimerPaused
TimerStopped

AnnouncementShown
AnnouncementHidden
```

---

# Security

Authentication required.

Controllers require login.

Display clients may optionally use signed URLs.

Rate limit API requests.

Audit log all operator actions.

---

# Future Enhancements

Version 2:

* Multi-screen output
* Multiple independent display channels
* Theme designer
* Drag-and-drop overlay editor
* OBS WebSocket integration
* NDI output
* PowerPoint import
* SongSelect integration
* Bible translation APIs

Version 3:

* Stage display
* Confidence monitor
* Mobile operator companion app
* AI-generated sermon lower thirds
* Automated scripture detection from sermon notes

---

# Success Criteria

The application is considered complete when:

1. OBS browser source displays overlays in real time.
2. Operators can remotely control graphics from any device.
3. Multiple display clients remain synchronized.
4. No page refreshes are required.
5. State is preserved after reconnecting.
6. Animations run smoothly at 60 FPS.
7. Mobile devices can fully control the system.
8. The system can be deployed on a standard Ubuntu VPS.
