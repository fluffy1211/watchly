import { createContext, useContext, useState } from 'react'
import { login as apiLogin } from '../api/auth'

const AuthContext = createContext(null)

function decodeToken(token) {
  try {
    const payload = token.split('.')[1]
    return JSON.parse(atob(payload))
  } catch {
    return null
  }
}

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem('watchly_token'))
  const [user, setUser] = useState(() => {
    const stored = localStorage.getItem('watchly_token')
    if (!stored) return null
    const payload = decodeToken(stored)
    return payload
      ? { id: payload.id, email: payload.email, username: payload.username, roles: payload.roles }
      : null
  })

  const login = async (email, password) => {
    const response = await apiLogin(email, password)
    const newToken = response.data.token
    localStorage.setItem('watchly_token', newToken)
    setToken(newToken)
    const payload = decodeToken(newToken)
    setUser({ id: payload.id, email: payload.email, username: payload.username, roles: payload.roles })
  }

  const logout = () => {
    localStorage.removeItem('watchly_token')
    setToken(null)
    setUser(null)
  }

  const isAdmin = () => user?.roles?.includes('ROLE_ADMIN') ?? false

  return (
    <AuthContext.Provider value={{ user, token, login, logout, isAdmin }}>
      {children}
    </AuthContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth() {
  return useContext(AuthContext)
}
