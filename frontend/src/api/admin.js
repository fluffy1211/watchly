import axiosInstance from './axiosInstance'

export const getUsers = () =>
  axiosInstance.get('/admin/users')

export const deleteUser = (id) =>
  axiosInstance.delete(`/admin/users/${id}`)
