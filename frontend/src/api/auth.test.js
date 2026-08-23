import axiosInstance from './axiosInstance'
import { login, register, requestPasswordReset, resetPassword } from './auth'

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

  it('requestPasswordReset posts email to /password-reset/request', async () => {
    axiosInstance.post.mockResolvedValue({ data: { message: 'ok' } })

    await requestPasswordReset('user@example.com')

    expect(axiosInstance.post).toHaveBeenCalledWith('/password-reset/request', {
      email: 'user@example.com',
    })
  })

  it('resetPassword posts token, password and confirmation to /password-reset/reset', async () => {
    axiosInstance.post.mockResolvedValue({ data: { message: 'ok' } })

    await resetPassword('token123', 'password123', 'password123')

    expect(axiosInstance.post).toHaveBeenCalledWith('/password-reset/reset', {
      token: 'token123',
      password: 'password123',
      password_confirmation: 'password123',
    })
  })
})
