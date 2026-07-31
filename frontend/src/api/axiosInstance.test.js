describe('axiosInstance', () => {
  beforeEach(() => {
    jest.resetModules()
    localStorage.clear()
    window.history.pushState({}, '', '/')
  })

  it('attaches the Authorization header when a token is stored', async () => {
    localStorage.setItem('watchly_token', 'abc123')
    const { default: axiosInstance } = await import('./axiosInstance')

    const config = axiosInstance.interceptors.request.handlers[0].fulfilled({ headers: {} })

    expect(config.headers.Authorization).toBe('Bearer abc123')
  })

  it('does not attach an Authorization header when there is no token', async () => {
    const { default: axiosInstance } = await import('./axiosInstance')

    const config = axiosInstance.interceptors.request.handlers[0].fulfilled({ headers: {} })

    expect(config.headers.Authorization).toBeUndefined()
  })

  it('clears the token on a 401 outside the auth page', async () => {
    const consoleError = jest.spyOn(console, 'error').mockImplementation(() => {})
    localStorage.setItem('watchly_token', 'abc123')
    window.history.pushState({}, '', '/collection')
    const { default: axiosInstance } = await import('./axiosInstance')
    const error = { response: { status: 401 } }

    await expect(
      axiosInstance.interceptors.response.handlers[0].rejected(error)
    ).rejects.toEqual(error)

    expect(localStorage.getItem('watchly_token')).toBeNull()
    consoleError.mockRestore()
  })

  it('does not clear the token on a 401 while already on the auth page', async () => {
    localStorage.setItem('watchly_token', 'abc123')
    window.history.pushState({}, '', '/auth')
    const { default: axiosInstance } = await import('./axiosInstance')
    const error = { response: { status: 401 } }

    await expect(
      axiosInstance.interceptors.response.handlers[0].rejected(error)
    ).rejects.toEqual(error)

    expect(localStorage.getItem('watchly_token')).toBe('abc123')
  })

  it('propagates non-401 errors without touching the token', async () => {
    localStorage.setItem('watchly_token', 'abc123')
    const { default: axiosInstance } = await import('./axiosInstance')
    const error = { response: { status: 500 } }

    await expect(
      axiosInstance.interceptors.response.handlers[0].rejected(error)
    ).rejects.toEqual(error)

    expect(localStorage.getItem('watchly_token')).toBe('abc123')
  })
})
