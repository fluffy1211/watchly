import axiosInstance from './axiosInstance'

export const login = (email, password) =>
  axiosInstance.post('/login', { email, password })

export const register = (email, password, username, consent) =>
  axiosInstance.post('/register', { email, password, username, consent })
