import axiosInstance from './axiosInstance'

export const login = (email, password) =>
  axiosInstance.post('/login', { email, password })

export const register = (email, password, username, consent) =>
  axiosInstance.post('/register', { email, password, username, consent })

export const requestPasswordReset = (email) =>
  axiosInstance.post('/password-reset/request', { email })

export const resetPassword = (token, password, passwordConfirmation) =>
  axiosInstance.post('/password-reset/reset', {
    token,
    password,
    password_confirmation: passwordConfirmation,
  })
