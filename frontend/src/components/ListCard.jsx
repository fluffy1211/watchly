import { Link } from 'react-router-dom'
import styles from './ListCard.module.css'

export default function ListCard({ list }) {
  return (
    <Link to={`/lists/${list.id}`} className={styles.card}>
      <div className={styles.header}>
        <h3 className={styles.title}>{list.title}</h3>
        {list.visibility === 'PRIVATE' && <span className={styles.badge}>Privée</span>}
      </div>
      {list.description && <p className={styles.description}>{list.description}</p>}
      <div className={styles.footer}>
        {list.owner && <span className={styles.owner}>par {list.owner.username}</span>}
        <span className={styles.filmCount}>{list.film_count} film{list.film_count > 1 ? 's' : ''}</span>
      </div>
    </Link>
  )
}
