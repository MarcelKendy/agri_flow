import axios from 'axios'
import { useAuthStore } from '@/stores/auth.js'

const baseURL = 'http://localhost/agro_track_project/backend/public/api/'
const api = axios.create({
  baseURL: baseURL,
  headers: {
    'Content-Type': 'application/json'
  }
})

api.interceptors.request.use(config => {
  const auth = useAuthStore()
  const token = auth?.token
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
}, error => {
  return Promise.reject(error)
})

api.interceptors.response.use(
  (response) => response, 
  (error) => {
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      console.log(error)
      const auth = useAuthStore()
      if (auth?.is_auth && auth?.user?.id && auth?.token) {
        auth.clear()
      }      
      return new Promise(() => {})
    } 
    return Promise.reject(error)   
  }
)

export { api, baseURL }
export default api
