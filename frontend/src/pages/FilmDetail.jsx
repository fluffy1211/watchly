import { useState, useEffect, useCallback } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { getById } from '../api/films'
import { getCollection, addFilm, updateStatus, toggleFavorite, removeFilm, updateRating } from '../api/collection'
import { getReviews, putReview } from '../api/reviews'
import { useAuth } from '../context/AuthContext'
import StarRating from '../components/ui/StarRating'
import WatchedModal from '../components/ui/WatchedModal'
import Button from '../components/ui/Button'
import Spinner from '../components/ui/Spinner'
import styles from './FilmDetail.module.css'

const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p/w500'

function formatRuntime(minutes) {
  if (!minutes) return null
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return `${h}h${m > 0 ? m.toString().padStart(2, '0') : ''}`
}

export default function FilmDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const { user } = useAuth()

  const [film, setFilm] = useState(null)
  const [entry, setEntry] = useState(null)
  const [reviews, setReviews] = useState([])
  const [loading, setLoading] = useState(true)
  const [actionLoading, setActionLoading] = useState(false)
  const [error, setError] = useState('')
  const [modalOpen, setModalOpen] = useState(false)
  const [modalLoading, setModalLoading] = useState(false)
  const [pendingWatchedPath, setPendingWatchedPath] = useState(null) // 'add' | 'update'

  const loadData = useCallback(async () => {
    setLoading(true)
    try {
      const filmRes = await getById(id)
      const filmData = filmRes.data
      setFilm(filmData)

      const colRes = await getCollection()
      const colData = colRes.data || []
      const found = colData.find(
        (e) => e.film?.tmdb_id === Number(id)
      )
      setEntry(found ? { ...found, isFavorite: found.is_favorite } : null)

      // Load reviews if the film has a local DB id
      if (filmData.localId || filmData.id) {
        try {
          const revRes = await getReviews(filmData.localId || filmData.id)
          setReviews((revRes.data || []).filter(
            (r) => (r.user?.id ?? r.userId) !== user?.id
          ))
        } catch {
          // Reviews may not exist yet
        }
      }
    } catch {
      setError('Impossible de charger le film')
    } finally {
      setLoading(false)
    }
  }, [id, user?.id])

  useEffect(() => {
    loadData()
  }, [loadData])

  const normalizeEntry = (raw) => ({
    id: raw.id,
    status: raw.status,
    isFavorite: raw.is_favorite,
    rating: raw.rating ?? null,
    film: raw.film,
  })

  const handleAddToWatchlist = async () => {
    setActionLoading(true)
    try {
      const res = await addFilm(Number(id))
      setEntry(normalizeEntry(res.data.collection))
    } catch { setError('Erreur lors de l\'ajout') }
    finally { setActionLoading(false) }
  }

  const handleOpenWatchedModal = (path) => {
    setPendingWatchedPath(path)
    setModalOpen(true)
  }

  const handleModalConfirm = async (rating, reviewText) => {
    setModalLoading(true)
    try {
      let collectionId

      if (pendingWatchedPath === 'add') {
        const res = await addFilm(Number(id), 'WATCHED')
        const col = res.data.collection
        setEntry(normalizeEntry(col))
        collectionId = col.id
      } else {
        if (!entry) throw new Error('entry missing')
        await updateStatus(entry.id, 'WATCHED')
        collectionId = entry.id
      }

      if (rating > 0) {
        await updateRating(collectionId, rating)
      }

      const filmId = film.localId || film.id
      if (reviewText.length >= 10) {
        await putReview(filmId, reviewText)
      }

      setModalOpen(false)
      setPendingWatchedPath(null)
      await loadData()
    } catch {
      setError('Erreur lors de la mise à jour')
    } finally {
      setModalLoading(false)
    }
  }

  const handleModalSkip = async () => {
    setModalLoading(true)
    try {
      if (pendingWatchedPath === 'add') {
        const res = await addFilm(Number(id), 'WATCHED')
        setEntry(normalizeEntry(res.data.collection))
      } else {
        await updateStatus(entry.id, 'WATCHED')
      }
      setModalOpen(false)
      setPendingWatchedPath(null)
      await loadData()
    } catch {
      setError('Erreur lors de la mise à jour')
    } finally {
      setModalLoading(false)
    }
  }

  const handleToggleFavorite = async () => {
    if (!entry) return
    setActionLoading(true)
    try {
      await toggleFavorite(entry.id, !entry.isFavorite)
      await loadData()
    } catch { setError('Erreur lors de la mise à jour') }
    finally { setActionLoading(false) }
  }

  const handleRemove = async () => {
    if (!entry) return
    setActionLoading(true)
    try {
      await removeFilm(entry.id)
      setEntry(null)
    } catch { setError('Erreur lors de la suppression') }
    finally { setActionLoading(false) }
  }

  if (loading) {
    return (
      <div className={styles.loadingWrap}>
        <Spinner size="lg" />
      </div>
    )
  }

  if (error && !film) {
    return <div className={styles.error}>{error}</div>
  }

  if (!film) return null

  const posterSrc = film.poster_path
    ? `${TMDB_IMAGE_BASE}${film.poster_path}`
    : null

  const year = film.release_date ? new Date(film.release_date).getFullYear() : '—'
  const runtime = formatRuntime(film.runtime)
  const score = film.vote_average ? Number(film.vote_average).toFixed(1) : null
  const status = entry?.status
  const isWatched = status === 'WATCHED' || entry?.isFavorite

  return (
    <div className={styles.page}>
      {/* Back link */}
      <button className={styles.backLink} onClick={() => navigate(-1)}>
        ← Retour aux résultats
      </button>

      <div className={styles.layout}>
        {/* Poster */}
        <div className={styles.posterCol}>
          {posterSrc ? (
            <img
              className={styles.poster}
              src={posterSrc}
              alt={`Affiche de ${film.title}`}
            />
          ) : (
            <div className={styles.posterPlaceholder}>🎬</div>
          )}
        </div>

        {/* Meta */}
        <div className={styles.metaCol}>
          {/* Genres */}
          {film.genres?.length > 0 && (
            <div className={styles.genres}>
              {film.genres.map((g) => (
                <span key={g.id || g.name} className={styles.genreTag}>
                  {g.name}
                </span>
              ))}
            </div>
          )}

          <h1 className={styles.filmTitle}>{film.title}</h1>

          {/* Stats row */}
          <div className={styles.stats}>
            <div className={styles.stat}>
              <span className={styles.statVal}>{year}</span>
              <span className={styles.statKey}>Année</span>
            </div>
            {runtime && (
              <div className={styles.stat}>
                <span className={styles.statVal}>{runtime}</span>
                <span className={styles.statKey}>Durée</span>
              </div>
            )}
            {score && (
              <div className={styles.stat}>
                <span className={styles.statVal}>{score}</span>
                <span className={styles.statKey}>Score TMDB</span>
              </div>
            )}
          </div>

          {/* Synopsis */}
          {film.overview && (
            <p className={styles.synopsis}>{film.overview}</p>
          )}

          {/* Star rating */}
          {isWatched && (
            <div className={styles.ratingSection}>
              <p className={styles.ratingLabel}>Votre note personnelle</p>
              <StarRating
                value={entry?.rating || 0}
                onChange={null}
              />
            </div>
          )}

          {/* Actions */}
          <div className={styles.actions}>
            {!entry && (
              <>
                <Button variant="secondary" onClick={handleAddToWatchlist} loading={actionLoading}>
                  ★ Watchlist
                </Button>
                <Button variant="primary" onClick={() => handleOpenWatchedModal('add')} loading={actionLoading}>
                  ✓ Marquer comme vu
                </Button>
              </>
            )}

            {status === 'WATCHLIST' && (
              <>
                <Button variant="primary" onClick={() => handleOpenWatchedModal('update')} loading={actionLoading}>
                  ✓ Marquer comme vu
                </Button>
                <Button variant="danger" onClick={handleRemove} loading={actionLoading}>
                  ✕ Retirer de ma liste
                </Button>
              </>
            )}

            {isWatched && (
              <>
                <Button variant="secondary" disabled className={styles.watchedBtn}>
                  ✓ Vu
                </Button>
                <Button
                  variant={entry?.isFavorite ? 'primary' : 'ghost'}
                  onClick={handleToggleFavorite}
                  loading={actionLoading}
                >
                  ★ Favori
                </Button>
                <Button variant="danger" onClick={handleRemove} loading={actionLoading}>
                  ✕ Retirer
                </Button>
              </>
            )}

            <a
              href={`https://www.themoviedb.org/movie/${id}`}
              target="_blank"
              rel="noopener noreferrer"
              className={styles.tmdbLink}
            >
              <Button variant="secondary">↗ TMDB</Button>
            </a>
          </div>

          {error && <p className={styles.inlineError}>{error}</p>}
        </div>
      </div>

      {/* Reviews section */}
      <section className={styles.reviewsSection}>
        <h2 className={styles.reviewsTitle}>Avis des spectateurs</h2>

        {reviews.length > 0 ? (
          <div className={styles.reviewsList}>
            {reviews.map((review) => (
              <div key={review.id} className={styles.reviewCard}>
                <div className={styles.reviewHeader}>
                  <span className={styles.reviewAuthor}>
                    {review.user?.username || review.username || 'Utilisateur'}
                  </span>
                  <span className={styles.reviewDate}>
                    {review.createdAt
                      ? new Date(review.createdAt).toLocaleDateString('fr-FR')
                      : ''}
                  </span>
                </div>
                <p className={styles.reviewContent}>{review.content}</p>
              </div>
            ))}
          </div>
        ) : (
          <p className={styles.noReviews}>Aucun avis pour le moment</p>
        )}
      </section>

      <WatchedModal
        isOpen={modalOpen}
        filmTitle={film?.title}
        onConfirm={handleModalConfirm}
        onSkip={handleModalSkip}
        loading={modalLoading}
      />
    </div>
  )
}
