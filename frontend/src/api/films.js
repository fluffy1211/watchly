import axiosInstance from './axiosInstance'

export const search = (query, page = 1) =>
  axiosInstance.get('/films/search', { params: { q: query, page } })

export const getById = (id) =>
  axiosInstance.get(`/films/${id}`)

export const getPopular = (page = 1) =>
  axiosInstance.get('/films/popular', { params: { page } })

export const getGenres = () =>
  axiosInstance.get('/films/genres')

export const discoverByGenre = (genreId, page = 1) =>
  axiosInstance.get('/films/discover', { params: { genre_id: genreId, page } })
