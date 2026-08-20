import styles from './Legal.module.css'
import { LAST_UPDATED } from './legalInfo'

export default function LegalPage({ title, children }) {
  return (
    <div className={styles.page}>
      <h1 className={styles.title}>{title}</h1>
      <p className={styles.updated}>Dernière mise à jour : {LAST_UPDATED}</p>
      {children}
    </div>
  )
}
