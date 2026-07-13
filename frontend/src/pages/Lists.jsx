import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { getLists } from '../api/lists'
import ListCard from '../components/ListCard'
import Button from '../components/ui/Button'
import Spinner from '../components/ui/Spinner'
import styles from './Lists.module.css'

export default function Lists() {
  const { token } = useAuth()
  const [lists, setLists] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')

  useEffect(() => {
    const load = async () => {
      setLoading(true)
      setError('')
      try {
        const res = await getLists(search)
        setLists(res.data || [])
      } catch {
        setError('Impossible de charger les listes')
      } finally {
        setLoading(false)
      }
    }
    const timeout = setTimeout(load, 300)
    return () => clearTimeout(timeout)
  }, [search])

  return (
    <div className={styles.page}>
      <div className={styles.header}>
        <h1 className={styles.title}>Listes</h1>
        {token && (
          <Link to="/lists/new">
            <Button variant="primary" size="sm">Créer une liste</Button>
          </Link>
        )}
      </div>

      <input
        className={styles.searchInput}
        type="text"
        placeholder="Rechercher une liste ou un utilisateur…"
        value={search}
        onChange={(e) => setSearch(e.target.value)}
      />

      {error && <p className={styles.error}>{error}</p>}

      {loading ? (
        <div className={styles.spinnerWrap}><Spinner /></div>
      ) : lists.length > 0 ? (
        <div className={styles.grid}>
          {lists.map((list) => (
            <ListCard key={list.id} list={list} />
          ))}
        </div>
      ) : (
        <p className={styles.empty}>Aucune liste trouvée.</p>
      )}
    </div>
  )
}
