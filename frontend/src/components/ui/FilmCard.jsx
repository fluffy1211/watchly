import styles from './FilmCard.module.css'

const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p/w500'

export default function FilmCard({ film, onClick }) {
  const year = film.release_date
    ? new Date(film.release_date).getFullYear()
    : '—'

  const posterSrc = film.poster_path
    ? `${TMDB_IMAGE_BASE}${film.poster_path}`
    : null

  const handleKeyDown = (e) => {
    if ((e.key === 'Enter' || e.key === ' ') && onClick) {
      e.preventDefault()
      onClick()
    }
  }

  return (
    <article className={styles.card} onClick={onClick} onKeyDown={handleKeyDown} role="button" tabIndex={0}>
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
      </div>
      <div className={styles.info}>
        <h3 className={styles.title}>{film.title}</h3>
        <span className={styles.year}>{year}</span>
      </div>
    </article>
  )
}
