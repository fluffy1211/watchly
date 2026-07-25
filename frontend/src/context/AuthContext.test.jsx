import { render, screen, act } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { AuthProvider, useAuth } from './AuthContext'
import { login as apiLogin } from '../api/auth'

jest.mock('../api/auth')

// header.payload.signature, payload = { id: 1, email: 'user@example.com', username: 'user', roles: ['ROLE_USER'] }
const FAKE_TOKEN =
  'eyJhbGciOiJIUzI1NiJ9.' +
  Buffer.from(JSON.stringify({ id: 1, email: 'user@example.com', username: 'user', roles: ['ROLE_USER'] })).toString('base64') +
  '.signature'

function Consumer() {
  const { user, token, login, logout } = useAuth()
  return (
    <div>
      <span data-testid="token">{token ?? 'no-token'}</span>
      <span data-testid="username">{user?.username ?? 'no-user'}</span>
      <button onClick={() => login('user@example.com', 'password123')}>login</button>
      <button onClick={logout}>logout</button>
    </div>
  )
}

describe('AuthContext', () => {
  beforeEach(() => {
    localStorage.clear()
    jest.clearAllMocks()
  })

  it('starts with no token when localStorage is empty', () => {
    render(
      <AuthProvider>
        <Consumer />
      </AuthProvider>
    )

    expect(screen.getByTestId('token')).toHaveTextContent('no-token')
    expect(screen.getByTestId('username')).toHaveTextContent('no-user')
  })

  it('restores user from a token already in localStorage', () => {
    localStorage.setItem('watchly_token', FAKE_TOKEN)

    render(
      <AuthProvider>
        <Consumer />
      </AuthProvider>
    )

    expect(screen.getByTestId('username')).toHaveTextContent('user')
  })

  it('login stores the token and decodes the user', async () => {
    apiLogin.mockResolvedValue({ data: { token: FAKE_TOKEN } })
    const user = userEvent.setup()

    render(
      <AuthProvider>
        <Consumer />
      </AuthProvider>
    )

    await act(() => user.click(screen.getByText('login')))

    expect(localStorage.getItem('watchly_token')).toBe(FAKE_TOKEN)
    expect(screen.getByTestId('username')).toHaveTextContent('user')
  })

  it('logout clears the token and user', async () => {
    localStorage.setItem('watchly_token', FAKE_TOKEN)
    const user = userEvent.setup()

    render(
      <AuthProvider>
        <Consumer />
      </AuthProvider>
    )

    await act(() => user.click(screen.getByText('logout')))

    expect(localStorage.getItem('watchly_token')).toBeNull()
    expect(screen.getByTestId('token')).toHaveTextContent('no-token')
  })
})
