import { useState, useEffect } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { register as apiRegister } from '../api/auth'
import Button from '../components/ui/Button'
import styles from './Auth.module.css'

export default function Auth() {
  const { token, login } = useAuth()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()

  const [activeTab, setActiveTab] = useState(
    searchParams.get('tab') === 'register' ? 'register' : 'login'
  )

  // Login state
  const [loginEmail, setLoginEmail] = useState('')
  const [loginPassword, setLoginPassword] = useState('')
  const [showLoginPw, setShowLoginPw] = useState(false)

  // Register state
  const [regUsername, setRegUsername] = useState('')
  const [regEmail, setRegEmail] = useState('')
  const [regPassword, setRegPassword] = useState('')
  const [regConfirm, setRegConfirm] = useState('')
  const [showRegPw, setShowRegPw] = useState(false)

  // Shared state
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  useEffect(() => {
    if (token) navigate('/search', { replace: true })
  }, [token, navigate])

  if (token) return null

  const handleLogin = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await login(loginEmail, loginPassword)
      navigate('/search', { replace: true })
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.error || 'Identifiants invalides'
      setError(msg)
    } finally {
      setLoading(false)
    }
  }

  const handleRegister = async (e) => {
    e.preventDefault()
    setError('')

    if (regPassword !== regConfirm) {
      setError('Les mots de passe ne correspondent pas')
      return
    }

    setLoading(true)
    try {
      await apiRegister(regEmail, regPassword, regUsername)
      await login(regEmail, regPassword)
      navigate('/search', { replace: true })
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.error || 'Erreur lors de l\'inscription'
      setError(msg)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className={styles.authPage}>
      <div className={styles.card}>
        <h1 className={styles.title}>Bienvenue</h1>
        <p className={styles.subtitle}>Connectez-vous pour accéder à votre collection</p>

        {/* Tabs */}
        <div className={styles.tabs}>
          <button
            className={`${styles.tab} ${activeTab === 'login' ? styles.tabActive : ''}`}
            onClick={() => { setActiveTab('login'); setError('') }}
            type="button"
          >
            Connexion
          </button>
          <button
            className={`${styles.tab} ${activeTab === 'register' ? styles.tabActive : ''}`}
            onClick={() => { setActiveTab('register'); setError('') }}
            type="button"
          >
            Inscription
          </button>
        </div>

        {error && <div className={styles.error}>{error}</div>}

        {/* Login form */}
        {activeTab === 'login' && (
          <form onSubmit={handleLogin}>
            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="login-email">Adresse email</label>
              <input
                id="login-email"
                className={styles.input}
                type="email"
                placeholder="gabriel@exemple.com"
                value={loginEmail}
                onChange={(e) => setLoginEmail(e.target.value)}
                required
              />
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="login-password">Mot de passe</label>
              <div className={styles.passwordWrap}>
                <input
                  id="login-password"
                  className={styles.input}
                  type={showLoginPw ? 'text' : 'password'}
                  placeholder="••••••••"
                  value={loginPassword}
                  onChange={(e) => setLoginPassword(e.target.value)}
                  required
                />
                <button
                  type="button"
                  className={styles.eyeToggle}
                  onClick={() => setShowLoginPw(!showLoginPw)}
                  aria-label={showLoginPw ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                >
                  {showLoginPw ? '🙈' : '👁'}
                </button>
              </div>
              <div className={styles.forgotRow}>
                <span className={styles.forgotLink}>Mot de passe oublié ?</span>
              </div>
            </div>

            <Button variant="primary" size="lg" type="submit" loading={loading} style={{ width: '100%' }}>
              Se connecter
            </Button>

            <p className={styles.legal}>
              En vous connectant, vous acceptez nos{' '}
              <span className={styles.legalLink}>CGU</span> et notre{' '}
              <span className={styles.legalLink}>Politique de confidentialité</span>
            </p>
          </form>
        )}

        {/* Register form */}
        {activeTab === 'register' && (
          <form onSubmit={handleRegister}>
            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="reg-username">Pseudo</label>
              <input
                id="reg-username"
                className={styles.input}
                type="text"
                placeholder="cinephile42"
                value={regUsername}
                onChange={(e) => setRegUsername(e.target.value)}
                required
              />
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="reg-email">Adresse email</label>
              <input
                id="reg-email"
                className={styles.input}
                type="email"
                placeholder="gabriel@exemple.com"
                value={regEmail}
                onChange={(e) => setRegEmail(e.target.value)}
                required
              />
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="reg-password">Mot de passe</label>
              <div className={styles.passwordWrap}>
                <input
                  id="reg-password"
                  className={styles.input}
                  type={showRegPw ? 'text' : 'password'}
                  placeholder="••••••••"
                  value={regPassword}
                  onChange={(e) => setRegPassword(e.target.value)}
                  required
                />
                <button
                  type="button"
                  className={styles.eyeToggle}
                  onClick={() => setShowRegPw(!showRegPw)}
                  aria-label={showRegPw ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                >
                  {showRegPw ? '🙈' : '👁'}
                </button>
              </div>
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="reg-confirm">Confirmer le mot de passe</label>
              <input
                id="reg-confirm"
                className={styles.input}
                type="password"
                placeholder="••••••••"
                value={regConfirm}
                onChange={(e) => setRegConfirm(e.target.value)}
                required
              />
            </div>

            <Button variant="primary" size="lg" type="submit" loading={loading} style={{ width: '100%' }}>
              Créer mon compte
            </Button>
          </form>
        )}
      </div>
    </div>
  )
}
