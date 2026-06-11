import axiosInstance from './axiosInstance'

export const getCollection = () =>
  axiosInstance.get('/collection')

export const addFilm = (tmdbId, status = null) => {
  const body = { tmdb_id: parseInt(tmdbId) }
  if (status) body.status = status
  return axiosInstance.post('/collection/add', body)
}

export const updateStatus = (id, status) =>
  axiosInstance.patch(`/collection/${id}/status`, { status })

export const toggleFavorite = (id, isFavorite) =>
  axiosInstance.patch(`/collection/${id}/favorite`, { is_favorite: isFavorite })

export const updateRating = (id, rating) =>
  axiosInstance.patch(`/collection/${id}/rating`, { rating })

export const removeFilm = (id) =>
  axiosInstance.delete(`/collection/${id}`)
