import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { getUsers, deleteUser, updateUserRoles } from '../api/admin'
import { getCommentReports, resolveCommentReport } from '../api/reports'
import ToastContainer from '../components/ui/Toast'
import { useToast } from '../components/ui/useToast'
import styles from './Admin.module.css'

const AVATAR_GRADIENTS = [
  'linear-gradient(135deg, #E8B86D, #D4A054)',
  'linear-gradient(135deg, #7C9EE8, #5B7FD4)',
  'linear-gradient(135deg, #74D4A8, #4ABDA0)',
  'linear-gradient(135deg, #E87C9E, #D45B7F)',
  'linear-gradient(135deg, #B87CE8, #9A5BD4)',
]

function avatarGradient(username) {
  const code = username ? username.charCodeAt(0) : 0
  return AVATAR_GRADIENTS[code % AVATAR_GRADIENTS.length]
}

function formatDate(iso) {
  if (!iso) return '—'
  const d = new Date(iso)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

export default function Admin() {
  const navigate = useNavigate()
  const { user, isAdmin, isSuperAdmin } = useAuth()
  const { toasts, showToast, removeToast } = useToast()
  const [users, setUsers] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [searchQuery, setSearchQuery] = useState('')
  const [pendingDelete, setPendingDelete] = useState(null)
  const [deleting, setDeleting] = useState(false)
  const [reports, setReports] = useState([])
  const [loadingReports, setLoadingReports] = useState(true)

  useEffect(() => {
    if (!isAdmin()) {
      navigate('/search', { replace: true })
      return
    }
    const load = async () => {
      setLoading(true)
      setError('')
      try {
        const res = await getUsers()
        setUsers(res.data || [])
      } catch {
        setError('Impossible de charger les utilisateurs')
      } finally {
        setLoading(false)
      }
    }
    const loadReports = async () => {
      setLoadingReports(true)
      try {
        const res = await getCommentReports()
        setReports(res.data || [])
      } catch {
        // silent fail
      } finally {
        setLoadingReports(false)
      }
    }
    load()
    loadReports()
  }, [isAdmin, navigate])

  const filtered = users.filter((u) => {
    if (!searchQuery.trim()) return true
    const q = searchQuery.toLowerCase()
    return u.username?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q)
  })

  const handleDeleteConfirm = async () => {
    if (!pendingDelete) return
    setDeleting(true)
    try {
      await deleteUser(pendingDelete.id)
      setUsers((prev) => prev.filter((u) => u.id !== pendingDelete.id))
      showToast(`Compte de ${pendingDelete.username} supprimé`, 'success')
      setPendingDelete(null)
    } catch {
      showToast('Erreur lors de la suppression', 'error')
    } finally {
      setDeleting(false)
    }
  }

  const handleRoleChange = async (target, grantAdmin) => {
    const roles = grantAdmin
      ? [...new Set([...target.roles, 'ROLE_ADMIN'])]
      : target.roles.filter((r) => r !== 'ROLE_ADMIN')
    try {
      const res = await updateUserRoles(target.id, roles)
      setUsers((prev) => prev.map((u) => (u.id === target.id ? { ...u, roles: res.data.user.roles } : u)))
      showToast(grantAdmin ? `${target.username} est maintenant admin` : `${target.username} n'est plus admin`, 'success')
    } catch {
      showToast('Erreur lors de la mise à jour du rôle', 'error')
    }
  }

  const handleResolveReport = async (reportId, action) => {
    try {
      await resolveCommentReport(reportId, action)
      setReports((prev) => prev.filter((r) => r.id !== reportId))
      showToast(action === 'delete' ? 'Commentaire supprimé' : 'Signalement classé', 'success')
    } catch {
      showToast('Erreur lors du traitement du signalement', 'error')
    }
  }

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Gestion des membres</h1>
          <p className={styles.subtitle}>{users.length} utilisateur{users.length !== 1 ? 's' : ''} inscrits</p>
        </div>
        <div className={styles.headerRight}>
          <span className={styles.adminBadge}>⚙ Administration</span>
          <input
            className={styles.searchInput}
            type="text"
            placeholder="Rechercher un membre…"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>
      </div>

      {error && <p className={styles.error}>{error}</p>}

      {loading ? (
        <div className={styles.loadingWrap}>
          <div className={styles.skeletonTable}>
            {Array.from({ length: 5 }).map((_, i) => (
              <div key={i} className={styles.skeletonRow} />
            ))}
          </div>
        </div>
      ) : (
        <div className={styles.tableWrap}>
          <table className={styles.table}>
            <thead>
              <tr>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Inscription</th>
                <th>Films</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((u) => (
                <tr key={u.id}>
                  <td>
                    <div className={styles.userCell}>
                      <div
                        className={styles.avatar}
                        style={{ background: avatarGradient(u.username) }}
                      >
                        {u.username?.[0]?.toUpperCase() ?? '?'}
                      </div>
                      <span className={styles.username}>{u.username}</span>
                    </div>
                  </td>
                  <td>
                    <span className={styles.mono}>{u.email}</span>
                  </td>
                  <td>
                    <span className={styles.mono}>{formatDate(u.created_at)}</span>
                  </td>
                  <td>
                    <span className={styles.mono}>{u.stats?.total_films ?? 0} films</span>
                  </td>
                  <td>
                    <span className={styles.statusDot}>
                      <span className={styles.dot} />
                      Actif
                    </span>
                  </td>
                  <td>
                    {u.id === user?.id ? (
                      <button className={styles.btnDisabled} disabled>
                        Compte admin
                      </button>
                    ) : (
                      <div className={styles.reportActions}>
                        {isSuperAdmin() && !u.roles.includes('ROLE_SUPER_ADMIN') && (
                          u.roles.includes('ROLE_ADMIN') ? (
                            <button className={styles.btnDanger} onClick={() => handleRoleChange(u, false)}>
                              Retirer admin
                            </button>
                          ) : (
                            <button className={styles.btnKeep} onClick={() => handleRoleChange(u, true)}>
                              Rendre admin
                            </button>
                          )
                        )}
                        <button
                          className={styles.btnDanger}
                          onClick={() => setPendingDelete(u)}
                        >
                          🗑 Supprimer
                        </button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
              {filtered.length === 0 && (
                <tr>
                  <td colSpan={6} className={styles.emptyRow}>
                    Aucun utilisateur trouvé
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}

      <div className={`${styles.header} ${styles.sectionHeader}`}>
        <div>
          <h1 className={styles.title}>Commentaires signalés</h1>
          <p className={styles.subtitle}>{reports.length} signalement{reports.length !== 1 ? 's' : ''} en attente</p>
        </div>
      </div>

      {loadingReports ? (
        <div className={styles.loadingWrap}>
          <div className={styles.skeletonTable}>
            {Array.from({ length: 3 }).map((_, i) => (
              <div key={i} className={styles.skeletonRow} />
            ))}
          </div>
        </div>
      ) : (
        <div className={styles.tableWrap}>
          <table className={styles.table}>
            <thead>
              <tr>
                <th>Commentaire</th>
                <th>Auteur</th>
                <th>Liste</th>
                <th>Signalé par</th>
                <th>Raison</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {reports.map((r) => (
                <tr key={r.id}>
                  <td>{r.comment.content}</td>
                  <td><span className={styles.username}>{r.comment.author.username}</span></td>
                  <td><span className={styles.mono}>{r.comment.list.title}</span></td>
                  <td><span className={styles.username}>{r.reporter.username}</span></td>
                  <td><span className={styles.mono}>{r.reason || '—'}</span></td>
                  <td>
                    <div className={styles.reportActions}>
                      <button className={styles.btnKeep} onClick={() => handleResolveReport(r.id, 'keep')}>
                        Conserver
                      </button>
                      <button className={styles.btnDanger} onClick={() => handleResolveReport(r.id, 'delete')}>
                        🗑 Supprimer
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {reports.length === 0 && (
                <tr>
                  <td colSpan={6} className={styles.emptyRow}>
                    Aucun signalement en attente
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}

      {pendingDelete && (
        <div className={styles.modalOverlay} onClick={() => setPendingDelete(null)}>
          <div className={styles.modal} onClick={(e) => e.stopPropagation()}>
            <h2 className={styles.modalTitle}>Supprimer ce compte ?</h2>
            <p className={styles.modalBody}>
              Cette action est irréversible et supprimera toutes les données associées
              (RGPD – Droit à l&apos;oubli).
            </p>
            <div className={styles.modalActions}>
              <button className={styles.btnCancel} onClick={() => setPendingDelete(null)}>
                Annuler
              </button>
              <button
                className={styles.btnConfirm}
                onClick={handleDeleteConfirm}
                disabled={deleting}
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
