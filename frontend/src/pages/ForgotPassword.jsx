import { useState } from 'react'
import { Link } from 'react-router-dom'
import { requestPasswordReset } from '../api/auth'
import Button from '../components/ui/Button'
import styles from './Auth.module.css'

export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [submitted, setSubmitted] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await requestPasswordReset(email)
      setSubmitted(true)
    } catch (err) {
      setError(err.response?.data?.message || 'Une erreur est survenue. Veuillez réessayer.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className={styles.authPage}>
      <div className={styles.card}>
        <h1 className={styles.title}>Mot de passe oublié</h1>

        {submitted ? (
          <>
            <p className={styles.success}>
              Si un compte existe avec cette adresse, un lien de réinitialisation a été envoyé.
            </p>
            <p className={styles.subtitle}>
              <Link className={styles.forgotLink} to="/auth">Retour à la connexion</Link>
            </p>
          </>
        ) : (
          <>
            <p className={styles.subtitle}>
              Saisissez votre adresse email pour recevoir un lien de réinitialisation.
            </p>
            {error && <div className={styles.error}>{error}</div>}
            <form onSubmit={handleSubmit}>
              <div className={styles.formGroup}>
                <label className={styles.label} htmlFor="forgot-email">Adresse email</label>
                <input
                  id="forgot-email"
                  className={styles.input}
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
              </div>
              <Button variant="primary" size="lg" type="submit" loading={loading} style={{ width: '100%' }}>
                Envoyer le lien
              </Button>
              <p className={styles.legal}>
                <Link className={styles.legalLink} to="/auth">Retour à la connexion</Link>
              </p>
            </form>
          </>
        )}
      </div>
    </div>
  )
}
