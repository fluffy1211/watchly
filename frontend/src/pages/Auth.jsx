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
  const [fieldErrors, setFieldErrors] = useState({})

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
      const msg = err.response?.status === 401
        ? 'Identifiants invalides'
        : (err.response?.data?.message || err.response?.data?.error || 'Identifiants invalides')
      setError(msg)
    } finally {
      setLoading(false)
    }
  }

  const handleRegister = async (e) => {
    e.preventDefault()
    setError('')
    setFieldErrors({})

    if (regPassword !== regConfirm) {
      setFieldErrors({ password_confirmation: 'Les mots de passe ne correspondent pas' })
      return
    }

    setLoading(true)
    try {
      await apiRegister(regEmail, regPassword, regUsername)
      await login(regEmail, regPassword)
      navigate('/search', { replace: true })
    } catch (err) {
      const data = err.response?.data
      if (data?.errors && typeof data.errors === 'object') {
        const mapped = {}
        for (const [key, val] of Object.entries(data.errors)) {
          mapped[key] = Array.isArray(val) ? val[0] : val
        }
        setFieldErrors(mapped)
        setError(data.message || Object.values(mapped).join('. '))
      } else {
        setError(data?.message || data?.error || 'Erreur lors de l\'inscription')
      }
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
            onClick={() => { setActiveTab('login'); setError(''); setFieldErrors({}) }}
            type="button"
          >
            Connexion
          </button>
          <button
            className={`${styles.tab} ${activeTab === 'register' ? styles.tabActive : ''}`}
            onClick={() => { setActiveTab('register'); setError(''); setFieldErrors({}) }}
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
                  {showLoginPw ? (
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  ) : (
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  )}
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
                className={`${styles.input} ${fieldErrors.username ? styles.inputError : ''}`}
                type="text"
                value={regUsername}
                onChange={(e) => setRegUsername(e.target.value)}
                required
              />
              {fieldErrors.username && <span className={styles.fieldError}>{fieldErrors.username}</span>}
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="reg-email">Adresse email</label>
              <input
                id="reg-email"
                className={`${styles.input} ${fieldErrors.email ? styles.inputError : ''}`}
                type="email"
                value={regEmail}
                onChange={(e) => setRegEmail(e.target.value)}
                required
              />
              {fieldErrors.email && <span className={styles.fieldError}>{fieldErrors.email}</span>}
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="reg-password">Mot de passe</label>
              <div className={styles.passwordWrap}>
                <input
                  id="reg-password"
                  className={`${styles.input} ${fieldErrors.password ? styles.inputError : ''}`}
                  type={showRegPw ? 'text' : 'password'}
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
                  {showRegPw ? (
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  ) : (
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  )}
                </button>
              </div>
              <span className={styles.hint}>Minimum 8 caractères</span>
              {fieldErrors.password && <span className={styles.fieldError}>{fieldErrors.password}</span>}
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="reg-confirm">Confirmer le mot de passe</label>
              <input
                id="reg-confirm"
                className={`${styles.input} ${fieldErrors.password_confirmation ? styles.inputError : ''}`}
                type="password"
                value={regConfirm}
                onChange={(e) => setRegConfirm(e.target.value)}
                required
              />
              {fieldErrors.password_confirmation && <span className={styles.fieldError}>{fieldErrors.password_confirmation}</span>}
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
