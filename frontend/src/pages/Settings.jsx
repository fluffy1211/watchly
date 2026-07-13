import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { changePassword as apiChangePassword, deleteAccount as apiDeleteAccount } from '../api/profile'
import Button from '../components/ui/Button'
import ToastContainer from '../components/ui/Toast'
import { useToast } from '../components/ui/useToast'
import styles from './Settings.module.css'

function parseFieldErrors(errors) {
  const mapped = {}
  ;(errors || []).forEach((e) => {
    const idx = e.indexOf(': ')
    if (idx > -1) mapped[e.slice(0, idx)] = e.slice(idx + 2)
  })
  return mapped
}

export default function Settings() {
  const { logout } = useAuth()
  const navigate = useNavigate()
  const { toasts, showToast, removeToast } = useToast()

  // Change password state
  const [currentPassword, setCurrentPassword] = useState('')
  const [newPassword, setNewPassword] = useState('')
  const [newPasswordConfirmation, setNewPasswordConfirmation] = useState('')
  const [showPasswords, setShowPasswords] = useState(false)
  const [fieldErrors, setFieldErrors] = useState({})
  const [changingPassword, setChangingPassword] = useState(false)

  // Delete account state
  const [showDeleteModal, setShowDeleteModal] = useState(false)
  const [deletePassword, setDeletePassword] = useState('')
  const [deleteError, setDeleteError] = useState('')
  const [deleting, setDeleting] = useState(false)

  const handleChangePassword = async (e) => {
    e.preventDefault()
    setFieldErrors({})

    if (newPassword !== newPasswordConfirmation) {
      setFieldErrors({ new_password_confirmation: 'Les mots de passe ne correspondent pas' })
      return
    }

    setChangingPassword(true)
    try {
      await apiChangePassword(currentPassword, newPassword, newPasswordConfirmation)
      showToast('Mot de passe mis à jour', 'success')
      setCurrentPassword('')
      setNewPassword('')
      setNewPasswordConfirmation('')
    } catch (err) {
      const data = err.response?.data
      if (data?.errors) {
        setFieldErrors(parseFieldErrors(data.errors))
      } else if (err.response?.status === 403) {
        setFieldErrors({ current_password: 'Mot de passe actuel incorrect' })
      } else {
        showToast(data?.message || 'Erreur lors du changement de mot de passe', 'error')
      }
    } finally {
      setChangingPassword(false)
    }
  }

  const openDeleteModal = () => {
    setDeletePassword('')
    setDeleteError('')
    setShowDeleteModal(true)
  }

  const handleDeleteConfirm = async () => {
    setDeleteError('')
    setDeleting(true)
    try {
      await apiDeleteAccount(deletePassword)
      setShowDeleteModal(false)
      logout()
      navigate('/', { replace: true })
    } catch (err) {
      const status = err.response?.status
      setDeleteError(
        status === 403
          ? 'Mot de passe incorrect'
          : err.response?.data?.message || 'Erreur lors de la suppression du compte'
      )
    } finally {
      setDeleting(false)
    }
  }

  return (
    <div className={styles.page}>
      <h1 className={styles.title}>Paramètres du compte</h1>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>Changer le mot de passe</h2>
        <form onSubmit={handleChangePassword} className={styles.form}>
          <div className={styles.formGroup}>
            <label className={styles.label} htmlFor="current-password">Mot de passe actuel</label>
            <input
              id="current-password"
              className={`${styles.input} ${fieldErrors.current_password ? styles.inputError : ''}`}
              type={showPasswords ? 'text' : 'password'}
              value={currentPassword}
              onChange={(e) => setCurrentPassword(e.target.value)}
              required
            />
            {fieldErrors.current_password && <span className={styles.fieldError}>{fieldErrors.current_password}</span>}
          </div>

          <div className={styles.formGroup}>
            <label className={styles.label} htmlFor="new-password">Nouveau mot de passe</label>
            <input
              id="new-password"
              className={`${styles.input} ${fieldErrors.new_password ? styles.inputError : ''}`}
              type={showPasswords ? 'text' : 'password'}
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
              required
            />
            <span className={styles.hint}>Minimum 8 caractères</span>
            {fieldErrors.new_password && <span className={styles.fieldError}>{fieldErrors.new_password}</span>}
          </div>

          <div className={styles.formGroup}>
            <label className={styles.label} htmlFor="new-password-confirmation">Confirmer le nouveau mot de passe</label>
            <input
              id="new-password-confirmation"
              className={`${styles.input} ${fieldErrors.new_password_confirmation ? styles.inputError : ''}`}
              type={showPasswords ? 'text' : 'password'}
              value={newPasswordConfirmation}
              onChange={(e) => setNewPasswordConfirmation(e.target.value)}
              required
            />
            {fieldErrors.new_password_confirmation && <span className={styles.fieldError}>{fieldErrors.new_password_confirmation}</span>}
          </div>

          <label className={styles.checkboxRow}>
            <input
              type="checkbox"
              checked={showPasswords}
              onChange={(e) => setShowPasswords(e.target.checked)}
            />
            Afficher les mots de passe
          </label>

          <Button variant="primary" type="submit" loading={changingPassword}>
            Mettre à jour le mot de passe
          </Button>
        </form>
      </section>

      <section className={`${styles.section} ${styles.dangerZone}`}>
        <h2 className={styles.sectionTitle}>Supprimer mon compte</h2>
        <p className={styles.dangerText}>
          Cette action est irréversible et supprimera définitivement votre profil, vos avis
          et votre collection (RGPD – Droit à l&apos;oubli).
        </p>
        <button className={styles.btnDanger} onClick={openDeleteModal}>
          Supprimer mon compte
        </button>
      </section>

      {showDeleteModal && (
        <div className={styles.modalOverlay} onClick={() => !deleting && setShowDeleteModal(false)}>
          <div className={styles.modal} onClick={(e) => e.stopPropagation()}>
            <h2 className={styles.modalTitle}>Confirmer la suppression</h2>
            <p className={styles.modalBody}>
              Cette action est définitive. Saisissez votre mot de passe pour confirmer
              la suppression de votre compte.
            </p>
            <div className={styles.formGroup}>
              <label className={styles.label} htmlFor="delete-password">Mot de passe</label>
              <input
                id="delete-password"
                className={`${styles.input} ${deleteError ? styles.inputError : ''}`}
                type="password"
                value={deletePassword}
                onChange={(e) => setDeletePassword(e.target.value)}
                autoFocus
              />
              {deleteError && <span className={styles.fieldError}>{deleteError}</span>}
            </div>
            <div className={styles.modalActions}>
              <button
                className={styles.btnCancel}
                onClick={() => setShowDeleteModal(false)}
                disabled={deleting}
              >
                Annuler
              </button>
              <button
                className={styles.btnConfirm}
                onClick={handleDeleteConfirm}
                disabled={deleting || !deletePassword}
              >
                {deleting ? 'Suppression…' : 'Confirmer la suppression'}
              </button>
            </div>
          </div>
        </div>
      )}

      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </div>
  )
}
