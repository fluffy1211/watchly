import { useState, useEffect } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import {
  getList, updateList, deleteList, addFilmToList, removeFilmFromList,
  getComments, postComment, deleteComment,
} from '../api/lists'
import { reportComment } from '../api/reports'
import FilmCard from '../components/ui/FilmCard'
import Button from '../components/ui/Button'
import Spinner from '../components/ui/Spinner'
import styles from './ListDetail.module.css'

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}

export default function ListDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const { user, token } = useAuth()

  const [list, setList] = useState(null)
  const [comments, setComments] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  const [editing, setEditing] = useState(false)
  const [titleValue, setTitleValue] = useState('')
  const [descriptionValue, setDescriptionValue] = useState('')
  const [visibilityValue, setVisibilityValue] = useState('PUBLIC')
  const [saving, setSaving] = useState(false)

  const [newFilmId, setNewFilmId] = useState('')
  const [addingFilm, setAddingFilm] = useState(false)
  const [filmError, setFilmError] = useState('')

  const [commentText, setCommentText] = useState('')
  const [postingComment, setPostingComment] = useState(false)
  const [reportedIds, setReportedIds] = useState(new Set())

  const isOwner = !!list && user?.username === list.owner?.username

  const reload = async () => {
    const res = await getList(id)
    setList(res.data)
    const commentsRes = await getComments(id)
    setComments(commentsRes.data || [])
  }

  useEffect(() => {
    const load = async () => {
      setLoading(true)
      setError('')
      try {
        await reload()
      } catch (err) {
        if (err.response?.status === 403) {
          setError('Cette liste est privée')
        } else if (err.response?.status === 404) {
          setError('Liste introuvable')
        } else {
          setError('Impossible de charger la liste')
        }
      } finally {
        setLoading(false)
      }
    }
    load()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id])

  const handleEditOpen = () => {
    setTitleValue(list.title)
    setDescriptionValue(list.description || '')
    setVisibilityValue(list.visibility)
    setEditing(true)
  }

  const handleEditSave = async () => {
    setSaving(true)
    try {
      const res = await updateList(id, {
        title: titleValue,
        description: descriptionValue,
        visibility: visibilityValue,
      })
      setList((prev) => ({ ...prev, ...res.data.list }))
      setEditing(false)
    } catch {
      // keep editing open on error
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async () => {
    if (!window.confirm('Supprimer définitivement cette liste ?')) return
    await deleteList(id)
    navigate('/lists')
  }

  const handleAddFilm = async (e) => {
    e.preventDefault()
    setFilmError('')
    setAddingFilm(true)
    try {
      await addFilmToList(id, parseInt(newFilmId, 10))
      setNewFilmId('')
      await reload()
    } catch (err) {
      setFilmError(err.response?.data?.message || 'Impossible d\'ajouter ce film')
    } finally {
      setAddingFilm(false)
    }
  }

  const handleRemoveFilm = async (tmdbId) => {
    await removeFilmFromList(id, tmdbId)
    await reload()
  }

  const handlePostComment = async (e) => {
    e.preventDefault()
    setPostingComment(true)
    try {
      await postComment(id, commentText)
      setCommentText('')
      const commentsRes = await getComments(id)
      setComments(commentsRes.data || [])
    } catch {
      // silent fail
    } finally {
      setPostingComment(false)
    }
  }

  const handleDeleteComment = async (commentId) => {
    await deleteComment(commentId)
    setComments((prev) => prev.filter((c) => c.id !== commentId))
  }

  const handleReportComment = async (commentId) => {
    const reason = window.prompt('Raison du signalement (optionnel) :')
    if (reason === null) return
    try {
      await reportComment(commentId, reason)
      setReportedIds((prev) => new Set(prev).add(commentId))
    } catch {
      // silent fail
    }
  }

  if (loading) {
    return <div className={styles.spinnerWrap}><Spinner /></div>
  }

  if (error) {
    return <div className={styles.page}><p className={styles.error}>{error}</p></div>
  }

  if (!list) return null

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        {editing ? (
          <div className={styles.editForm}>
            <input
              className={styles.input}
              value={titleValue}
              onChange={(e) => setTitleValue(e.target.value)}
              maxLength={255}
            />
            <textarea
              className={styles.textarea}
              value={descriptionValue}
              onChange={(e) => setDescriptionValue(e.target.value)}
              rows={2}
            />
            <select
              className={styles.input}
              value={visibilityValue}
              onChange={(e) => setVisibilityValue(e.target.value)}
            >
              <option value="PUBLIC">Publique</option>
              <option value="PRIVATE">Privée</option>
            </select>
            <div className={styles.editActions}>
              <Button variant="ghost" size="sm" onClick={() => setEditing(false)} disabled={saving}>
                Annuler
              </Button>
              <Button variant="primary" size="sm" onClick={handleEditSave} loading={saving}>
                Enregistrer
              </Button>
            </div>
          </div>
        ) : (
          <>
            <div>
              <h1 className={styles.title}>
                {list.title}
                {list.visibility === 'PRIVATE' && <span className={styles.badge}>Privée</span>}
              </h1>
              {list.description && <p className={styles.description}>{list.description}</p>}
              <p className={styles.meta}>par {list.owner.username} · créée le {formatDate(list.created_at)}</p>
            </div>
            {isOwner && (
              <div className={styles.ownerActions}>
                <Button variant="ghost" size="sm" onClick={handleEditOpen}>Modifier</Button>
                <button className={styles.deleteBtn} onClick={handleDelete}>Supprimer</button>
              </div>
            )}
          </>
        )}
      </div>

      {isOwner && (
        <form onSubmit={handleAddFilm} className={styles.addFilmForm}>
          <input
            className={styles.input}
            type="number"
            placeholder="ID TMDB du film à ajouter"
            value={newFilmId}
            onChange={(e) => setNewFilmId(e.target.value)}
            required
          />
          <Button variant="secondary" size="sm" type="submit" loading={addingFilm}>Ajouter</Button>
        </form>
      )}
      {filmError && <p className={styles.error}>{filmError}</p>}

      {list.films.length > 0 ? (
        <div className={styles.grid}>
          {list.films.map((film) => (
            <div key={film.tmdb_id} className={styles.cardWrap}>
              <FilmCard film={film} onClick={() => navigate(`/film/${film.tmdb_id}`)} />
              {isOwner && (
                <button className={styles.removeFilm} onClick={() => handleRemoveFilm(film.tmdb_id)} title="Retirer">
                  ✕
                </button>
              )}
            </div>
          ))}
        </div>
      ) : (
        <p className={styles.empty}>Cette liste est vide pour le moment.</p>
      )}

      <section className={styles.commentsSection}>
        <h2 className={styles.commentsTitle}>Commentaires ({comments.length})</h2>

        {token && (
          <form onSubmit={handlePostComment} className={styles.commentForm}>
            <textarea
              className={styles.textarea}
              value={commentText}
              onChange={(e) => setCommentText(e.target.value)}
              placeholder="Ajouter un commentaire…"
              rows={2}
              maxLength={2000}
              required
            />
            <Button variant="primary" size="sm" type="submit" loading={postingComment}>
              Commenter
            </Button>
          </form>
        )}

        <div className={styles.commentList}>
          {comments.map((comment) => (
            <div key={comment.id} className={styles.comment}>
              <div className={styles.commentHeader}>
                <Link to={`/profile/${comment.author.username}`} className={styles.commentAuthor}>
                  {comment.author.username}
                </Link>
                <span className={styles.commentDate}>{formatDate(comment.created_at)}</span>
              </div>
              <p className={styles.commentContent}>{comment.content}</p>
              <div className={styles.commentActions}>
                {user?.username === comment.author.username && (
                  <button className={styles.commentDelete} onClick={() => handleDeleteComment(comment.id)}>
                    Supprimer
                  </button>
                )}
                {token && user?.username !== comment.author.username && (
                  reportedIds.has(comment.id) ? (
                    <span className={styles.commentReported}>Signalé</span>
                  ) : (
                    <button className={styles.commentReport} onClick={() => handleReportComment(comment.id)}>
                      Signaler
                    </button>
                  )
                )}
              </div>
            </div>
          ))}
        </div>
      </section>
    </div>
  )
}
