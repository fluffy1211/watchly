import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import Header from './Header'
import { useAuth } from '../../context/AuthContext'

jest.mock('../../context/AuthContext')

function renderHeader() {
  return render(
    <MemoryRouter>
      <Header />
    </MemoryRouter>
  )
}

describe('Header', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('shows login and register links when not authenticated', () => {
    useAuth.mockReturnValue({ token: null, user: null, logout: jest.fn() })

    renderHeader()

    expect(screen.getByText('Connexion')).toBeInTheDocument()
    expect(screen.getByText("S'inscrire")).toBeInTheDocument()
    expect(screen.queryByText('Déconnexion')).not.toBeInTheDocument()
  })

  it('shows navigation links and username when authenticated', () => {
    useAuth.mockReturnValue({ token: 'abc', user: { username: 'gabriel' }, logout: jest.fn() })

    renderHeader()

    expect(screen.getByText('Ma Collection')).toBeInTheDocument()
    expect(screen.getByText('gabriel')).toBeInTheDocument()
    expect(screen.getByText('Déconnexion')).toBeInTheDocument()
  })

  it('calls logout when the logout button is clicked', async () => {
    const logout = jest.fn()
    const user = userEvent.setup()
    useAuth.mockReturnValue({ token: 'abc', user: { username: 'gabriel' }, logout })

    renderHeader()
    await user.click(screen.getByText('Déconnexion'))

    expect(logout).toHaveBeenCalledTimes(1)
  })
})
