import { useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { resetPassword } from '../api/auth'
import Button from '../components/ui/Button'
import styles from './Auth.module.css'

export default function ResetPassword() {
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token')

  const [password, setPassword] = useState('')
  const [confirmation, setConfirmation] = useState('')
  const [showPw, setShowPw] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    if (password !== confirmation) {
      setError('Les mots de passe ne correspondent pas')
      return
    }
    setLoading(true)
    try {
      await resetPassword(token, password, confirmation)
      setSuccess(true)
    } catch (err) {
      setError(err.response?.data?.message || 'Lien invalide ou expiré')
    } finally {
      setLoading(false)
    }
  }

  if (!token) {
    return (
      <div className={styles.authPage}>
        <div className={styles.card}>
          <h1 className={styles.title}>Réinitialiser le mot de passe</h1>
          <p className={styles.error}>Lien invalide ou expiré</p>
          <p className={styles.legal}>
            <Link className={styles.legalLink} to="/forgot-password">Demander un nouveau lien</Link>
          </p>
        </div>
      </div>
    )
  }

  if (success) {
    return (
      <div className={styles.authPage}>
        <div className={styles.card}>
          <h1 className={styles.title}>Réinitialiser le mot de passe</h1>
          <p className={styles.success}>Votre mot de passe a été mis à jour.</p>
          <p className={styles.legal}>
            <Link className={styles.legalLink} to="/auth">Se connecter</Link>
          </p>
        </div>
      </div>
    )
  }

  return (
    <div className={styles.authPage}>
      <div className={styles.card}>
        <h1 className={styles.title}>Réinitialiser le mot de passe</h1>
        <p className={styles.subtitle}>Choisissez un nouveau mot de passe.</p>

        {error && <div className={styles.error}>{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className={styles.formGroup}>
            <label className={styles.label} htmlFor="reset-password">Nouveau mot de passe</label>
            <div className={styles.passwordWrap}>
              <input
                id="reset-password"
                className={styles.input}
                type={showPw ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
              <button
                type="button"
                className={styles.eyeToggle}
                onClick={() => setShowPw(!showPw)}
                aria-label={showPw ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
              >
                {showPw ? (
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                ) : (
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                )}
              </button>
            </div>
            <span className={styles.hint}>Minimum 8 caractères</span>
          </div>

          <div className={styles.formGroup}>
            <label className={styles.label} htmlFor="reset-confirm">Confirmer le mot de passe</label>
            <input
              id="reset-confirm"
              className={styles.input}
              type={showPw ? 'text' : 'password'}
              value={confirmation}
              onChange={(e) => setConfirmation(e.target.value)}
              required
            />
          </div>

          <Button variant="primary" size="lg" type="submit" loading={loading} style={{ width: '100%' }}>
            Réinitialiser
          </Button>
        </form>
      </div>
    </div>
  )
}
