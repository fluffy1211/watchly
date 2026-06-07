import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useEffect } from 'react'
import Button from '../components/ui/Button'
import styles from './Landing.module.css'

const features = [
  {
    icon: '🔍',
    title: 'Recherche en temps réel',
    desc: 'Accédez à des millions de films via l\'API TMDB instantanément, avec synopsis, casting et affiches.',
  },
  {
    icon: '📋',
    title: 'Watchlist personnelle',
    desc: 'Sauvegardez les films à voir et marquez ceux que vous avez regardés, avec une note personnelle.',
  },
  {
    icon: '⭐',
    title: 'Notez vos films',
    desc: 'Attribuez une note de 1 à 5 étoiles pour retrouver facilement vos coups de cœur cinéma.',
  },
]

export default function Landing() {
  const { token } = useAuth()
  const navigate = useNavigate()

  useEffect(() => {
    if (token) navigate('/search', { replace: true })
  }, [token, navigate])

  if (token) return null

  return (
    <div className={styles.landing}>
      {/* Hero */}
      <section className={styles.hero}>
        <div className={styles.heroPattern} />
        <div className={styles.heroBadge}>✦ Votre collection. Votre cinéma.</div>
        <h1 className={styles.heroTitle}>
          Suivez chaque<br />film que vous <em>aimez</em>
        </h1>
        <p className={styles.heroSub}>
          Recherchez, ajoutez à votre Watchlist, notez vos films et retrouvez
          toute votre histoire cinématographique en un seul endroit.
        </p>
        <div className={styles.heroActions}>
          <Button variant="primary" size="lg" onClick={() => navigate('/auth?tab=register')}>
            Commencer gratuitement
          </Button>
        </div>
      </section>

      {/* Features */}
      <section className={styles.features}>
        {features.map((f) => (
          <article key={f.title} className={styles.featureCard}>
            <div className={styles.featureIcon}>{f.icon}</div>
            <h3 className={styles.featureTitle}>{f.title}</h3>
            <p className={styles.featureDesc}>{f.desc}</p>
          </article>
        ))}
      </section>
    </div>
  )
}
