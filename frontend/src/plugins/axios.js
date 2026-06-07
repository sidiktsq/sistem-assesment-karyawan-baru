import axios from 'axios'

export default defineNuxtPlugin(() => {
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  axios.defaults.baseURL = apiUrl
  
  // Set up interceptor for auth token from localStorage on the client side
  axios.interceptors.request.use(
    config => {
      if (import.meta.client) {
        const token = localStorage.getItem('auth_token')
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
      }
      return config
    },
    error => Promise.reject(error)
  )

  // Interceptor untuk handle response errors
  axios.interceptors.response.use(
    response => response,
    error => {
      // Handle 401 Unauthorized
      if (error.response?.status === 401 && import.meta.client) {
        localStorage.removeItem('auth_token')
        window.location.href = '/login'
      }
      return Promise.reject(error)
    }
  )
})
