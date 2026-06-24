import { useState, useEffect, useRef } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { getProfile, updateBio, uploadAvatar, deleteAvatar } from '../api/profile'
import Avatar from '../components/ui/Avatar'
import FilmCard from '../components/ui/FilmCard'
import Button from '../components/ui/Button'
import Spinner from '../components/ui/Spinner'
import styles from './Profile.module.css'

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}

export default function Profile() {
  const { username } = useParams()
  const navigate = useNavigate()
  const { user } = useAuth()
  const fileInputRef = useRef(null)

  const [profile, setProfile] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  const [editingBio, setEditingBio] = useState(false)
  const [bioValue, setBioValue] = useState('')
  const [bioSaving, setBioSaving] = useState(false)

  const [avatarUploading, setAvatarUploading] = useState(false)

  const isOwner = user?.username === username

  useEffect(() => {
    const load = async () => {
      setLoading(true)
      setError('')
      try {
        const res = await getProfile(username)
        setProfile(res.data)
      } catch (err) {
        if (err.response?.status === 404) {
          setError('Utilisateur introuvable')
        } else {
          setError('Impossible de charger le profil')
        }
      } finally {
        setLoading(false)
      }
    }
    load()
  }, [username])

  const handleBioEdit = () => {
    setBioValue(profile.bio || '')
    setEditingBio(true)
  }

  const handleBioSave = async () => {
    setBioSaving(true)
    try {
      const res = await updateBio(bioValue)
      setProfile((prev) => ({ ...prev, bio: res.data.bio }))
      setEditingBio(false)
    } catch {
      // keep editing open on error
    } finally {
      setBioSaving(false)
    }
  }

  const handleBioCancel = () => {
    setEditingBio(false)
    setBioValue(profile.bio || '')
  }

  const handleAvatarClick = () => {
    if (isOwner) fileInputRef.current?.click()
  }

  const handleAvatarChange = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    setAvatarUploading(true)
    try {
      const res = await uploadAvatar(file)
      setProfile((prev) => ({ ...prev, avatar_url: res.data.avatar_url }))
    } catch {
      // silent fail
    } finally {
      setAvatarUploading(false)
      e.target.value = ''
    }
  }

  const handleAvatarDelete = async () => {
    setAvatarUploading(true)
    try {
      await deleteAvatar()
      setProfile((prev) => ({ ...prev, avatar_url: null }))
    } catch {
      // silent fail
    } finally {
      setAvatarUploading(false)
    }
  }

  if (loading) {
    return <div className={styles.spinnerWrap}><Spinner /></div>
  }

  if (error) {
    return <div className={styles.page}><p className={styles.error}>{error}</p></div>
  }

  if (!profile) return null

  return (
    <div className={styles.page}>
      {/* Profile Header */}
      <div className={styles.profileHeader}>
        <div className={styles.avatarSection}>
          <div
            className={`${styles.avatarWrap} ${isOwner ? styles.avatarClickable : ''}`}
            onClick={handleAvatarClick}
          >
            <Avatar
              username={profile.username}
              avatarUrl={profile.avatar_url}
              size={96}
            />
            {isOwner && (
              <div className={styles.avatarOverlay}>
                {avatarUploading ? '...' : '📷'}
              </div>
            )}
          </div>
          {isOwner && profile.avatar_url && (
            <button className={styles.removeAvatar} onClick={handleAvatarDelete} disabled={avatarUploading}>
              Supprimer la photo
            </button>
          )}
          <input
            ref={fileInputRef}
            type="file"
            accept="image/jpeg,image/png,image/webp,image/gif"
            className={styles.hiddenInput}
            onChange={handleAvatarChange}
          />
        </div>

        <div className={styles.infoSection}>
          <h1 className={styles.username}>{profile.username}</h1>
          <p className={styles.memberSince}>Membre depuis {formatDate(profile.member_since)}</p>

          {editingBio ? (
            <div className={styles.bioEdit}>
              <textarea
                className={styles.bioTextarea}
                value={bioValue}
                onChange={(e) => setBioValue(e.target.value)}
                maxLength={500}
                placeholder="Décrivez-vous en quelques mots…"
                rows={3}
              />
              <div className={styles.bioActions}>
                <span className={styles.bioCount}>{bioValue.length}/500</span>
                <Button variant="ghost" size="sm" onClick={handleBioCancel} disabled={bioSaving}>
                  Annuler
                </Button>
                <Button variant="primary" size="sm" onClick={handleBioSave} loading={bioSaving}>
                  Enregistrer
                </Button>
              </div>
            </div>
          ) : (
            <div
              className={`${styles.bioDisplay} ${isOwner ? styles.bioEditable : ''}`}
              onClick={isOwner ? handleBioEdit : undefined}
            >
              {profile.bio ? (
                <p className={styles.bio}>{profile.bio}</p>
              ) : isOwner ? (
                <p className={styles.bioEmpty}>Aucune description</p>
              ) : null}
              {isOwner && <span className={styles.pencilIcon}>✎</span>}
            </div>
          )}
        </div>
      </div>

      {/* Stats */}
      <div className={styles.statsRow}>
        <div className={styles.statCard}>
          <span className={styles.statNum}>{profile.stats.watched}</span>
          <span className={styles.statLabel}>Films vus</span>
        </div>
        <div className={styles.statCard}>
          <span className={styles.statNum}>{profile.stats.favorites}</span>
          <span className={styles.statLabel}>Favoris</span>
        </div>
        <div className={styles.statCard}>
          <span className={styles.statNum}>
            {profile.stats.average_rating !== null ? `${profile.stats.average_rating}★` : '—'}
          </span>
          <span className={styles.statLabel}>Note moyenne</span>
        </div>
      </div>

      {/* Watched Films */}
      <div className={styles.section}>
        <h2 className={styles.sectionTitle}>
          Films vus
          <span className={styles.sectionCount}>{profile.stats.watched}</span>
        </h2>

        {profile.watched_films.length > 0 ? (
          <div className={styles.grid}>
            {profile.watched_films.map((film) => (
              <div key={film.tmdb_id} className={styles.cardWrap}>
                <FilmCard
                  film={film}
                  onClick={() => navigate(`/film/${film.tmdb_id}`)}
                />
              </div>
            ))}
          </div>
        ) : (
          <p className={styles.empty}>
            {isOwner ? 'Vous n\'avez pas encore marqué de film comme vu.' : 'Aucun film vu pour le moment.'}
          </p>
        )}
      </div>
    </div>
  )
}
