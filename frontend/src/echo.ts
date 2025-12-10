import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
    EchoDebug?: any
  }
}

window.Pusher = Pusher

Pusher.logToConsole = true

const createEcho = (token: string | null) => {
  console.log('[Echo] Creating Echo instance. Has token:', !!token)
  
  const echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY as string,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER as string,
    forceTLS: true,
    authEndpoint: `${import.meta.env.VITE_API_BASE_URL}/broadcasting/auth`,
    auth: {
      headers: token
        ? {
          Authorization: `Bearer ${token}`,
        }
        : {},
    },
  })
  
  // Log connection state changes
  // @ts-ignore - internal type not exposed
  echo.connector.pusher.connection.bind('state_change', (states: any) => {
    console.log('[Echo] Connection state change:', states)
  })
  
  // For debugging in DevTools
  window.EchoDebug = echo
  
  return echo
}

export default createEcho
