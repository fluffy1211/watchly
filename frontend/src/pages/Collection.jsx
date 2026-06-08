import { useState, useEffect, useMemo } from 'react'
import { Link, useSearchParams, useNavigate } from 'react-router-dom'
import { getCollection } from '../api/collection'
import FilmCard from '../components/ui/FilmCard'
import Spinner from '../components/ui/Spinner'
import styles from './Collection.module.css'

export default function Collection() {
  const [searchParams, setSearchParams] = useSearchParams()
  const navigate = useNavigate()
  const [entries, setEntries] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [titleFilter, setTitleFilter] = useState('')
  const [ratingFilter, setRatingFilter] = useState(0)

  const tab = searchParams.get('tab') || 'watchlist'

  useEffect(() => {
    const load = async () => {
      setLoading(true)
      setError('')
      try {
        const res = await getCollection()
        setEntries(res.data || [])
      } catch {
        setError('Impossible de charger votre collection')
      } finally {
        setLoading(false)
      }
    }
    load()
  }, [])

  const watchlist = useMemo(() => entries.filter((e) => e.status === 'WATCHLIST'), [entries])
  const watched = useMemo(() => entries.filter((e) => e.status === 'WATCHED'), [entries])

  const watchedCount = watched.length
  const watchlistCount = watchlist.length
  const avgRating = useMemo(() => {
    const rated = watched.filter((e) => e.rating !== null && e.rating !== undefined)
    if (!rated.length) return null
    return (rated.reduce((sum, e) => sum + e.rating, 0) / rated.length).toFixed(1)
  }, [watched])

  const currentList = tab === 'watched' ? watched : watchlist

  const filtered = useMemo(() => {
    let list = currentList
    if (titleFilter.trim()) {
      const q = titleFilter.trim().toLowerCase()
      list = list.filter((e) => e.film?.title?.toLowerCase().includes(q))
    }
    if (ratingFilter > 0 && tab === 'watched') {
      list = list.filter((e) => e.rating !== null && e.rating >= ratingFilter)
    }
    return list
  }, [currentList, titleFilter, ratingFilter, tab])

  const setTab = (t) => {
    setSearchParams({ tab: t })
    setRatingFilter(0)
    setTitleFilter('')
  }

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <h1 className={styles.title}>Ma Collection</h1>
        <div className={styles.tabs}>
          <button
            className={`${styles.tab} ${tab === 'watchlist' ? styles.tabActive : ''}`}
            onClick={() => setTab('watchlist')}
          >
            Watchlist
            <span className={styles.tabCount}>{watchlistCount}</span>
          </button>
          <button
            className={`${styles.tab} ${tab === 'watched' ? styles.tabActive : ''}`}
            onClick={() => setTab('watched')}
          >
            ✓ Films Vus
            <span className={styles.tabCount}>{watchedCount}</span>
          </button>
        </div>
      </div>

      <div className={styles.statsRow}>
        <div className={styles.statCard}>
          <span className={styles.statNum}>{watchedCount}</span>
          <span className={styles.statLabel}>Films vus</span>
        </div>
        <div className={styles.statCard}>
          <span className={styles.statNum}>{watchlistCount}</span>
          <span className={styles.statLabel}>À voir</span>
        </div>
        <div className={styles.statCard}>
          <span className={styles.statNum}>{avgRating !== null ? `${avgRating}★` : '—'}</span>
          <span className={styles.statLabel}>Note moyenne</span>
        </div>
      </div>

      <div className={styles.filtersRow}>
        <span className={styles.filterLabel}>Filtrer :</span>
        <button
          className={`${styles.filterChip} ${ratingFilter === 0 ? styles.filterChipActive : ''}`}
          onClick={() => setRatingFilter(0)}
        >
          Tous
        </button>
        {tab === 'watched' && [5, 4, 3].map((r) => (
          <button
            key={r}
            className={`${styles.filterChip} ${ratingFilter === r ? styles.filterChipActive : ''}`}
            onClick={() => setRatingFilter(r)}
          >
            {'★'.repeat(r)}
          </button>
        ))}
        <input
          className={styles.searchInput}
          type="text"
          placeholder="Filtrer par titre…"
          value={titleFilter}
          onChange={(e) => setTitleFilter(e.target.value)}
        />
      </div>

      {error && <p className={styles.error}>{error}</p>}

      {loading ? (
        <div className={styles.spinnerWrap}><Spinner /></div>
      ) : filtered.length > 0 ? (
        <div className={styles.grid}>
          {filtered.map((entry) => (
            <div key={entry.id} className={styles.cardWrap}>
              <FilmCard
                film={entry.film}
                collectionEntry={entry}
                onClick={() => navigate(`/film/${entry.film.tmdb_id}`)}
              />
              {entry.is_favorite && (
                <span className={styles.favoriteOverlay} title="Favori">★</span>
              )}
            </div>
          ))}
        </div>
      ) : (
        <div className={styles.empty}>
          {tab === 'watchlist' ? (
            <p>
              Votre Watchlist est vide.{' '}
              <Link to="/search" className={styles.emptyLink}>Découvrez des films →</Link>
            </p>
          ) : (
            <p>Vous n&apos;avez pas encore marqué de film comme vu.</p>
          )}
        </div>
      )}
    </div>
  )
}
