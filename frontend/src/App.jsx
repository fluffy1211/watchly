import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import ProtectedRoute from './components/ProtectedRoute'
import Landing from './pages/Landing'
import Auth from './pages/Auth'
import Search from './pages/Search'
import FilmDetail from './pages/FilmDetail'
import Collection from './pages/Collection'
import Admin from './pages/Admin'

function AuthRoute({ children }) {
  const { token } = useAuth()
  return token ? <Navigate to="/search" replace /> : children
}

function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<Landing />} />
      <Route path="/auth" element={<AuthRoute><Auth /></AuthRoute>} />
      <Route path="/search" element={<ProtectedRoute><Search /></ProtectedRoute>} />
      <Route path="/film/:id" element={<ProtectedRoute><FilmDetail /></ProtectedRoute>} />
      <Route path="/collection" element={<ProtectedRoute><Collection /></ProtectedRoute>} />
      <Route path="/admin" element={<ProtectedRoute requiredRole="ROLE_ADMIN"><Admin /></ProtectedRoute>} />
    </Routes>
  )
}

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </BrowserRouter>
  )
}
