import axiosInstance from './axiosInstance'

export const getReviews = (filmId) =>
  axiosInstance.get(`/films/${filmId}/reviews`)

export const putReview = (filmId, content) =>
  axiosInstance.put(`/films/${filmId}/review`, { content })
