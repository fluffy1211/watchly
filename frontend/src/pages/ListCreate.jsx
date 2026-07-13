import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { createList } from '../api/lists'
import Button from '../components/ui/Button'
import styles from './ListCreate.module.css'

export default function ListCreate() {
  const navigate = useNavigate()
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [visibility, setVisibility] = useState('PUBLIC')
  const [error, setError] = useState('')
  const [saving, setSaving] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setSaving(true)
    try {
      const res = await createList(title, description, visibility)
      navigate(`/lists/${res.data.list.id}`)
    } catch (err) {
      setError(err.response?.data?.message || 'Impossible de créer la liste')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className={styles.page}>
      <h1 className={styles.title}>Créer une liste</h1>

      <form onSubmit={handleSubmit} className={styles.form}>
        <div className={styles.formGroup}>
          <label className={styles.label} htmlFor="list-title">Titre</label>
          <input
            id="list-title"
            className={styles.input}
            type="text"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            maxLength={255}
            required
          />
        </div>

        <div className={styles.formGroup}>
          <label className={styles.label} htmlFor="list-description">Description</label>
          <textarea
            id="list-description"
            className={styles.textarea}
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            rows={3}
            placeholder="Décrivez votre liste…"
          />
        </div>

        <div className={styles.formGroup}>
          <label className={styles.label} htmlFor="list-visibility">Visibilité</label>
          <select
            id="list-visibility"
            className={styles.input}
            value={visibility}
            onChange={(e) => setVisibility(e.target.value)}
          >
            <option value="PUBLIC">Publique</option>
            <option value="PRIVATE">Privée</option>
          </select>
        </div>

        {error && <p className={styles.error}>{error}</p>}

        <Button variant="primary" type="submit" loading={saving}>
          Créer la liste
        </Button>
      </form>
    </div>
  )
}
