import { render, screen } from '@testing-library/react'
import { MemoryRouter, Routes, Route } from 'react-router-dom'
import ProtectedRoute from './ProtectedRoute'
import { useAuth } from '../context/AuthContext'

jest.mock('../context/AuthContext')

function renderWithRouter(requiredRole) {
  return render(
    <MemoryRouter initialEntries={['/protected']}>
      <Routes>
        <Route path="/auth" element={<div>auth page</div>} />
        <Route path="/search" element={<div>search page</div>} />
        <Route
          path="/protected"
          element={
            <ProtectedRoute requiredRole={requiredRole}>
              <div>protected content</div>
            </ProtectedRoute>
          }
        />
      </Routes>
    </MemoryRouter>
  )
}

describe('ProtectedRoute', () => {
  it('redirects to /auth when there is no token', () => {
    useAuth.mockReturnValue({ token: null, user: null })

    renderWithRouter()

    expect(screen.getByText('auth page')).toBeInTheDocument()
  })

  it('renders children when authenticated and no role is required', () => {
    useAuth.mockReturnValue({ token: 'abc', user: { roles: ['ROLE_USER'] } })

    renderWithRouter()

    expect(screen.getByText('protected content')).toBeInTheDocument()
  })

  it('redirects to /search when the required role is missing', () => {
    useAuth.mockReturnValue({ token: 'abc', user: { roles: ['ROLE_USER'] } })

    renderWithRouter('ROLE_ADMIN')

    expect(screen.getByText('search page')).toBeInTheDocument()
  })

  it('renders children when the required role is present', () => {
    useAuth.mockReturnValue({ token: 'abc', user: { roles: ['ROLE_USER', 'ROLE_ADMIN'] } })

    renderWithRouter('ROLE_ADMIN')

    expect(screen.getByText('protected content')).toBeInTheDocument()
  })
})
