import axios from 'axios'
import { useRouter } from 'vue-router'

export function useAuth() {
  const router = useRouter()
  
  const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost'
  
  const logout = async () => {
    const token = localStorage.getItem('auth_token')
    
    try {
      if (token) {
        await axios.post(
          `${API_BASE_URL}/api/logout`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          },
        )
      }
    } catch (e) {
      console.warn('Logout request failed, ignoring…', e)
    }
    
    localStorage.removeItem('auth_token')
    
    // Redirect
    await router.push('/login')
  }
  
  return { logout }
}
