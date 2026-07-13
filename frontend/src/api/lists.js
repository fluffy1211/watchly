import axiosInstance from './axiosInstance'

export const getLists = (q = '') =>
  axiosInstance.get('/lists', { params: q ? { q } : {} })

export const getList = (id) =>
  axiosInstance.get(`/lists/${id}`)

export const createList = (title, description, visibility) =>
  axiosInstance.post('/lists', { title, description, visibility })

export const updateList = (id, data) =>
  axiosInstance.patch(`/lists/${id}`, data)

export const deleteList = (id) =>
  axiosInstance.delete(`/lists/${id}`)

export const addFilmToList = (id, tmdbId) =>
  axiosInstance.post(`/lists/${id}/films/${tmdbId}`)

export const removeFilmFromList = (id, tmdbId) =>
  axiosInstance.delete(`/lists/${id}/films/${tmdbId}`)

export const getComments = (id) =>
  axiosInstance.get(`/lists/${id}/comments`)

export const postComment = (id, content) =>
  axiosInstance.post(`/lists/${id}/comments`, { content })

export const deleteComment = (commentId) =>
  axiosInstance.delete(`/comments/${commentId}`)
