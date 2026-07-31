import axiosInstance from './axiosInstance'
import {
  getProfile,
  updateBio,
  uploadAvatar,
  deleteAvatar,
  changePassword,
  deleteAccount,
} from './profile'

jest.mock('./axiosInstance')

describe('profile api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('getProfile calls GET /profile/:username', () => {
    getProfile('gabriel')
    expect(axiosInstance.get).toHaveBeenCalledWith('/profile/gabriel')
  })

  it('updateBio puts bio to /profile', () => {
    updateBio('hello world')
    expect(axiosInstance.put).toHaveBeenCalledWith('/profile', { bio: 'hello world' })
  })

  it('uploadAvatar posts a FormData with multipart headers', () => {
    const file = new File(['content'], 'avatar.png', { type: 'image/png' })

    uploadAvatar(file)

    expect(axiosInstance.post).toHaveBeenCalledWith(
      '/profile/avatar',
      expect.any(FormData),
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    const formData = axiosInstance.post.mock.calls[0][1]
    expect(formData.get('avatar')).toBe(file)
  })

  it('deleteAvatar deletes /profile/avatar', () => {
    deleteAvatar()
    expect(axiosInstance.delete).toHaveBeenCalledWith('/profile/avatar')
  })

  it('changePassword puts current, new and confirmation passwords', () => {
    changePassword('old123', 'new123', 'new123')
    expect(axiosInstance.put).toHaveBeenCalledWith('/profile/password', {
      current_password: 'old123',
      new_password: 'new123',
      new_password_confirmation: 'new123',
    })
  })

  it('deleteAccount deletes /profile with password in the request body', () => {
    deleteAccount('secret')
    expect(axiosInstance.delete).toHaveBeenCalledWith('/profile', { data: { password: 'secret' } })
  })
})
