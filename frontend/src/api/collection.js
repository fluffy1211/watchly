import axiosInstance from './axiosInstance'

export const getCollection = () =>
  axiosInstance.get('/collection')

export const addFilm = (tmdbId) =>
  axiosInstance.post('/collection/add', { tmdbId })

export const updateStatus = (id, status) =>
  axiosInstance.patch(`/collection/${id}/status`, { status })

export const toggleFavorite = (id, isFavorite) =>
  axiosInstance.patch(`/collection/${id}/favorite`, { isFavorite })

export const updateRating = (id, rating) =>
  axiosInstance.patch(`/collection/${id}/rating`, { rating })

export const removeFilm = (id) =>
  axiosInstance.delete(`/collection/${id}`)
