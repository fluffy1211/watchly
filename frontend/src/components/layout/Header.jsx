import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import Button from '../ui/Button'
import styles from './Header.module.css'

export default function Header() {
  const { token, logout } = useAuth()
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
            <Link to="/search" className={styles.navLink}>Recherche</Link>
            <Link to="/collection" className={styles.navLink}>Ma Collection</Link>
            <Button variant="ghost" size="sm" onClick={handleLogout} className={styles.logoutBtn}>
              Déconnexion
            </Button>
          </div>
        ) : (
          <div className={styles.right}>
            <Link to="/auth?tab=login">
              <Button variant="secondary" size="sm">Connexion</Button>
            </Link>
            <Link to="/auth?tab=register">
              <Button variant="primary" size="sm">S'inscrire</Button>
            </Link>
          </div>
        )}
      </div>
    </header>
  )
}
