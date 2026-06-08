import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { search as searchFilms, getPopular } from '../api/films'
import { getCollection } from '../api/collection'
import FilmCard from '../components/ui/FilmCard'
import Button from '../components/ui/Button'
import styles from './Search.module.css'

export default function Search() {
  const navigate = useNavigate()
  const [query, setQuery] = useState('')
  const [films, setFilms] = useState([])
  const [collection, setCollection] = useState([])
  const [loading, setLoading] = useState(true)
  const [isSearchResult, setIsSearchResult] = useState(false)
  const [error, setError] = useState('')

  // Load popular films + user collection on mount
  useEffect(() => {
    const loadInitial = async () => {
      setLoading(true)
      try {
        const [popularRes, collectionRes] = await Promise.all([
          getPopular(),
          getCollection(),
        ])
        setFilms(popularRes.data || [])
        setCollection(collectionRes.data || [])
      } catch {
        setError('Impossible de charger les films')
      } finally {
        setLoading(false)
      }
    }
    loadInitial()
  }, [])

  const handleSearch = async (e) => {
    e.preventDefault()
    if (!query.trim()) return

    setLoading(true)
    setError('')
    try {
      const res = await searchFilms(query.trim())
      setFilms(res.data || [])
      setIsSearchResult(true)
    } catch {
      setError('Erreur lors de la recherche')
    } finally {
      setLoading(false)
    }
  }

  const findCollectionEntry = (tmdbId) =>
    collection.find((entry) => entry.film?.tmdbId === tmdbId || entry.tmdbId === tmdbId)

  return (
    <div className={styles.page}>
      {/* Header */}
      <div className={styles.header}>
        <h1 className={styles.title}>Découvrir des Films</h1>
        <form className={styles.searchBar} onSubmit={handleSearch}>
          <div className={styles.inputWrap}>
            <span className={styles.searchIcon}>🔍</span>
            <input
              className={styles.input}
              type="text"
              placeholder="Titre du film…"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
            />
          </div>
          <Button variant="primary" type="submit" loading={loading}>
            Rechercher
          </Button>
        </form>
        {isSearchResult && !loading && (
          <p className={styles.resultsCount}>
            {films.length} résultat{films.length !== 1 ? 's' : ''} · <span className={styles.source}>Source : API TMDB</span>
          </p>
        )}
        {!isSearchResult && !loading && (
          <p className={styles.sectionLabel}>Films populaires</p>
        )}
      </div>

      {/* Error */}
      {error && <p className={styles.error}>{error}</p>}

      {/* Loading skeleton */}
      {loading && (
        <div className={styles.grid}>
          {Array.from({ length: 12 }).map((_, i) => (
            <div key={i} className={styles.skeleton}>
              <div className={styles.skeletonPoster} />
              <div className={styles.skeletonInfo}>
                <div className={styles.skeletonLine} />
                <div className={styles.skeletonLineShort} />
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Films grid */}
      {!loading && films.length > 0 && (
        <div className={styles.grid}>
          {films.map((film) => (
            <FilmCard
              key={film.tmdb_id || film.id}
              film={film}
              collectionEntry={findCollectionEntry(film.tmdb_id || film.id)}
              onClick={() => navigate(`/film/${film.tmdb_id || film.id}`)}
            />
          ))}
        </div>
      )}

      {/* Empty state */}
      {!loading && films.length === 0 && isSearchResult && (
        <div className={styles.empty}>
          <p>Aucun film trouvé pour cette recherche</p>
        </div>
      )}
    </div>
  )
}
