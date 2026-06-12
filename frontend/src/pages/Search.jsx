import { useState, useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { search as searchFilms, getPopular, getGenres, discoverByGenre } from '../api/films'
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
  const [loadingMore, setLoadingMore] = useState(false)
  const [isSearchResult, setIsSearchResult] = useState(false)
  const [error, setError] = useState('')
  const [genres, setGenres] = useState([])
  const [selectedGenre, setSelectedGenre] = useState(null)
  const [currentPage, setCurrentPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [totalResults, setTotalResults] = useState(0)
  const [searchCurrentPage, setSearchCurrentPage] = useState(1)
  const [searchTotalPages, setSearchTotalPages] = useState(1)
  const [activeSearchQuery, setActiveSearchQuery] = useState('')
  const [genreScrolled, setGenreScrolled] = useState(false)
  const genreBarRef = useRef(null)

  useEffect(() => {
    const loadInitial = async () => {
      setLoading(true)
      try {
        const [popularRes, genresRes, collectionRes] = await Promise.all([
          getPopular(1),
          getGenres(),
          getCollection(),
        ])
        const popular = popularRes.data
        setFilms(popular.films || [])
        setCurrentPage(popular.page || 1)
        setTotalPages(popular.total_pages || 1)
        setGenres(genresRes.data?.genres || [])
        setCollection(collectionRes.data || [])
      } catch {
        setError('Impossible de charger les films')
      } finally {
        setLoading(false)
      }
    }
    loadInitial()
  }, [])

  useEffect(() => {
    const el = genreBarRef.current
    if (!el) return
    const onScroll = () => setGenreScrolled(el.scrollLeft > 0)
    el.addEventListener('scroll', onScroll, { passive: true })
    return () => el.removeEventListener('scroll', onScroll)
  }, [genres])

  const handleSearch = async (e) => {
    e.preventDefault()
    if (!query.trim()) return

    setLoading(true)
    setError('')
    setSelectedGenre(null)
    try {
      const trimmed = query.trim()
      const res = await searchFilms(trimmed, 1)
      setFilms(res.data.results || [])
      setTotalResults(res.data.total_results || 0)
      setSearchCurrentPage(res.data.page || 1)
      setSearchTotalPages(res.data.total_pages || 1)
      setActiveSearchQuery(trimmed)
      setIsSearchResult(true)
      setCurrentPage(1)
      setTotalPages(1)
    } catch {
      setError('Erreur lors de la recherche')
    } finally {
      setLoading(false)
    }
  }

  const handleGenreSelect = async (genre) => {
    const next = selectedGenre?.id === genre?.id ? null : genre
    setSelectedGenre(next)
    setIsSearchResult(false)
    setLoading(true)
    setError('')
    try {
      const res = next
        ? await discoverByGenre(next.id, 1)
        : await getPopular(1)
      const data = res.data
      setFilms(data.films || [])
      setCurrentPage(data.page || 1)
      setTotalPages(data.total_pages || 1)
    } catch {
      setError('Impossible de charger les films')
    } finally {
      setLoading(false)
    }
  }

  const handleLoadMore = async () => {
    const nextPage = currentPage + 1
    setLoadingMore(true)
    try {
      const res = selectedGenre
        ? await discoverByGenre(selectedGenre.id, nextPage)
        : await getPopular(nextPage)
      const data = res.data
      setFilms((prev) => [...prev, ...(data.films || [])])
      setCurrentPage(data.page || nextPage)
      setTotalPages(data.total_pages || totalPages)
    } catch {
      setError('Impossible de charger plus de films')
    } finally {
      setLoadingMore(false)
    }
  }

  const handleLoadMoreSearch = async () => {
    const nextPage = searchCurrentPage + 1
    setLoadingMore(true)
    try {
      const res = await searchFilms(activeSearchQuery, nextPage)
      setFilms((prev) => [...prev, ...(res.data.results || [])])
      setSearchCurrentPage(res.data.page || nextPage)
      setSearchTotalPages(res.data.total_pages || searchTotalPages)
    } catch {
      setError('Impossible de charger plus de résultats')
    } finally {
      setLoadingMore(false)
    }
  }

  const findCollectionEntry = (tmdbId) =>
    collection.find((entry) => entry.film?.tmdbId === tmdbId || entry.tmdbId === tmdbId)

  const showLoadMore = !isSearchResult && !loading && currentPage < totalPages
  const showLoadMoreSearch = isSearchResult && !loading && searchCurrentPage < searchTotalPages

  return (
    <div className={styles.page}>
      {/* Header */}
      <div className={styles.header}>
        <h1 className={styles.title}>Découvrir des Films</h1>
        <form className={styles.searchBar} onSubmit={handleSearch}>
          <div className={styles.inputWrap}>
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

        {/* Genre chips */}
        {genres.length > 0 && !isSearchResult && (
          <div className={`${styles.genreBarWrap} ${genreScrolled ? styles.genreBarScrolled : ''}`}>
          <div className={styles.genreBar} ref={genreBarRef}>
            {genres.map((genre) => (
              <button
                key={genre.id}
                className={`${styles.genreChip} ${selectedGenre?.id === genre.id ? styles.genreChipActive : ''}`}
                onClick={() => handleGenreSelect(genre)}
                type="button"
              >
                {genre.name}
              </button>
            ))}
          </div>
          </div>
        )}

        {isSearchResult && !loading && (
          <p className={styles.resultsCount}>
            {totalResults} résultat{totalResults !== 1 ? 's' : ''} ·{' '}
          </p>
        )}
        {!isSearchResult && !loading && (
          <p className={styles.sectionLabel}>
            {selectedGenre ? selectedGenre.name : 'Films populaires'}
          </p>
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

      {/* Load More */}
      {showLoadMore && (
        <div className={styles.loadMoreWrap}>
          <Button variant="secondary" onClick={handleLoadMore} loading={loadingMore}>
            Charger plus
          </Button>
        </div>
      )}

      {showLoadMoreSearch && (
        <div className={styles.loadMoreWrap}>
          <Button variant="secondary" onClick={handleLoadMoreSearch} loading={loadingMore}>
            Charger plus
          </Button>
        </div>
      )}
    </div>
  )
}
