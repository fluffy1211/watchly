import styles from './FilmCard.module.css'
import Badge from './Badge'

const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p/w500'

function statusToVariant(status) {
  switch (status) {
    case 'WATCHLIST': return 'watchlist'
    case 'WATCHED':   return 'watched'
    case 'FAVORITE':  return 'favorite'
    default:          return 'watchlist'
  }
}

export default function FilmCard({ film, collectionEntry = null, onClick }) {
  const year = film.release_date
    ? new Date(film.release_date).getFullYear()
    : '—'

  const posterSrc = film.poster_path
    ? `${TMDB_IMAGE_BASE}${film.poster_path}`
    : null

  return (
    <article className={styles.card} onClick={onClick} role="button" tabIndex={0}>
      <div className={styles.posterWrap}>
        {posterSrc ? (
          <img
            className={styles.poster}
            src={posterSrc}
            alt={`Affiche de ${film.title}`}
            loading="lazy"
          />
        ) : (
          <div className={styles.placeholder}>
            <span>🎬</span>
          </div>
        )}
        {collectionEntry && (
          <div className={styles.badge}>
            <Badge variant={statusToVariant(collectionEntry.status)} />
          </div>
        )}
      </div>
      <div className={styles.info}>
        <h3 className={styles.title}>{film.title}</h3>
        <span className={styles.year}>{year}</span>
      </div>
    </article>
  )
}
