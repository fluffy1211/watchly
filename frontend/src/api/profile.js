import axiosInstance from './axiosInstance'

export const getProfile = (username) =>
  axiosInstance.get(`/profile/${username}`)

export const updateBio = (bio) =>
  axiosInstance.put('/profile', { bio })

export const uploadAvatar = (file) => {
  const formData = new FormData()
  formData.append('avatar', file)
  return axiosInstance.post('/profile/avatar', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}

export const deleteAvatar = () =>
  axiosInstance.delete('/profile/avatar')
