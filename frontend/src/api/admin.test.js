import axiosInstance from './axiosInstance'
import { getUsers, deleteUser, updateUserRoles } from './admin'

jest.mock('./axiosInstance')

describe('admin api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('getUsers calls GET /admin/users', () => {
    getUsers()
    expect(axiosInstance.get).toHaveBeenCalledWith('/admin/users')
  })

  it('deleteUser deletes the user endpoint', () => {
    deleteUser(5)
    expect(axiosInstance.delete).toHaveBeenCalledWith('/admin/users/5')
  })

  it('updateUserRoles patches roles', () => {
    updateUserRoles(5, ['ROLE_ADMIN'])
    expect(axiosInstance.patch).toHaveBeenCalledWith('/admin/users/5', { roles: ['ROLE_ADMIN'] })
  })
})
