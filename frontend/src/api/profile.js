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

export const changePassword = (currentPassword, newPassword, newPasswordConfirmation) =>
  axiosInstance.put('/profile/password', {
    current_password: currentPassword,
    new_password: newPassword,
    new_password_confirmation: newPasswordConfirmation,
  })

export const deleteAccount = (password) =>
  axiosInstance.delete('/profile', { data: { password } })
