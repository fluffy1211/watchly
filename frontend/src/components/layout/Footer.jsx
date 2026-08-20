import { Link } from 'react-router-dom'
import styles from './Footer.module.css'

export default function Footer() {
  return (
    <footer className={styles.footer}>
      <nav className={styles.links}>
        <Link className={styles.link} to="/cgu">CGU</Link>
        <Link className={styles.link} to="/confidentialite">Politique de confidentialité</Link>
        <Link className={styles.link} to="/mentions-legales">Mentions légales</Link>
      </nav>
      <p className={styles.credit}>
        Données films fournies par TMDB. Watchly n'est ni approuvé ni certifié par TMDB.
      </p>
    </footer>
  )
}
