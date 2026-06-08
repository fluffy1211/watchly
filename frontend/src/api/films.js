import axiosInstance from './axiosInstance'

export const search = (query) =>
  axiosInstance.get('/films/search', { params: { q: query } })

export const getById = (id) =>
  axiosInstance.get(`/films/${id}`)

export const getPopular = () =>
  axiosInstance.get('/films/popular')
