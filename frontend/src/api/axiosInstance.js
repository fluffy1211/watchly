import axios from 'axios'

const axiosInstance = axios.create({
  baseURL: '/api',
})

axiosInstance.interceptors.request.use((config) => {
  const token = localStorage.getItem('watchly_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('watchly_token')
      window.location.href = '/auth'
    }
    return Promise.reject(error)
  }
)

export default axiosInstance
