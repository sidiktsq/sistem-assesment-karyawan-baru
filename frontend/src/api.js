import axios from 'axios'

// Create axios instance dengan base URL dari environment
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// Interceptor untuk menambahkan token jika ada
apiClient.interceptors.request.use(
  config => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => Promise.reject(error)
)

// Interceptor untuk handle response errors
apiClient.interceptors.response.use(
  response => response,
  error => {
    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Utility functions untuk common API calls
export const setAuthToken = (token) => {
  if (token) {
    localStorage.setItem('auth_token', token)
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
}

export const clearAuth = () => {
  localStorage.removeItem('auth_token')
  delete apiClient.defaults.headers.common['Authorization']
}

export const getAuthToken = () => {
  return localStorage.getItem('auth_token')
}

// Assessment endpoints
export const assessmentAPI = {
  getAssessment: (id) => apiClient.get(`/assessments/${id}`),
  startAssessment: (data) => apiClient.post('/assessments/start', data),
  submitAssessment: (id, data) => apiClient.post(`/assessments/${id}/submit`, data),
  getResults: (id) => apiClient.get(`/assessments/${id}/results`),
}

// Review endpoints
export const reviewAPI = {
  getReviews: () => apiClient.get('/reviews'),
  getReview: (id) => apiClient.get(`/reviews/${id}`),
  submitReview: (id, data) => apiClient.post(`/reviews/${id}`, data),
  updateReview: (id, data) => apiClient.put(`/reviews/${id}`, data),
}

// Candidate endpoints
export const candidateAPI = {
  getProfile: () => apiClient.get('/candidate/profile'),
  updateProfile: (data) => apiClient.put('/candidate/profile', data),
  getInvitations: () => apiClient.get('/candidate/invitations'),
}

// Auth endpoints
export const authAPI = {
  login: (credentials) => apiClient.post('/login', credentials),
  logout: () => apiClient.post('/logout'),
  register: (data) => apiClient.post('/register', data),
  getCurrentUser: () => apiClient.get('/user'),
}

export default apiClient
