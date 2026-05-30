import { Navigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function ProtectedRoute({ children, requiredRole }) {
  const { token, user } = useAuth()

  if (!token) {
    return <Navigate to="/auth" replace />
  }

  if (requiredRole && !user?.roles?.includes(requiredRole)) {
    return <Navigate to="/search" replace />
  }

  return children
}
