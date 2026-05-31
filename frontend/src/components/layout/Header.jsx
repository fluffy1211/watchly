import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import Button from '../ui/Button'
import styles from './Header.module.css'

export default function Header() {
  const { user, token, logout } = useAuth()
  const navigate = useNavigate()
  const isAuthenticated = !!token

  const handleLogout = () => {
    logout()
    navigate('/')
  }

  return (
    <header className={styles.header}>
      <div className={styles.inner}>
        <Link to="/" className={styles.logo}>
          Watchly
        </Link>

        {isAuthenticated ? (
          <div className={styles.right}>
            <nav className={styles.nav}>
              <Link to="/search" className={styles.navLink}>Recherche</Link>
              <Link to="/collection" className={styles.navLink}>Ma Collection</Link>
            </nav>
            <div className={styles.avatar} title={user?.username || user?.email}>
              {(user?.username || user?.email || '?')[0].toUpperCase()}
            </div>
            <Button variant="ghost" size="sm" onClick={handleLogout}>
              Déconnexion
            </Button>
          </div>
        ) : (
          <div className={styles.right}>
            <Link to="/auth">
              <Button variant="ghost" size="sm">Connexion</Button>
            </Link>
            <Link to="/auth">
              <Button variant="primary" size="sm">S'inscrire</Button>
            </Link>
          </div>
        )}
      </div>
    </header>
  )
}
