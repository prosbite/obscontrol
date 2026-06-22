import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const reverbHost = (() => {
  const host = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname

  // On Windows, `localhost` often resolves to IPv6 `::1`, while Reverb is
  // usually bound to IPv4 during local development. Prefer an IPv4 loopback
  // address so the browser connects reliably.
  if (host === 'localhost' || host === '::1') {
    return '127.0.0.1'
  }

  return host
})()

const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080)
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http'

const echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: reverbHost,
  wsPort: reverbPort,
  wssPort: reverbPort,
  forceTLS: reverbScheme === 'https',
  enabledTransports: ['ws', 'wss'],
})

export default echo
