import axiosInstance from './axiosInstance'
import { login, register } from './auth'

jest.mock('./axiosInstance')

describe('auth api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('login posts email and password to /login', async () => {
    axiosInstance.post.mockResolvedValue({ data: { token: 'abc' } })

    const response = await login('user@example.com', 'password123')

    expect(axiosInstance.post).toHaveBeenCalledWith('/login', {
      email: 'user@example.com',
      password: 'password123',
    })
    expect(response.data.token).toBe('abc')
  })

  it('login propagates errors from axios', async () => {
    axiosInstance.post.mockRejectedValue({ response: { status: 401 } })

    await expect(login('user@example.com', 'wrong')).rejects.toEqual({
      response: { status: 401 },
    })
  })

  it('register posts email, password, username and consent to /register', async () => {
    axiosInstance.post.mockResolvedValue({ data: { message: 'ok' } })

    await register('user@example.com', 'password123', 'newuser', true)

    expect(axiosInstance.post).toHaveBeenCalledWith('/register', {
      email: 'user@example.com',
      password: 'password123',
      username: 'newuser',
      consent: true,
    })
  })
})
