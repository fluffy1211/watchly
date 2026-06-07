import { useState } from 'react'
import StarRating from './StarRating'
import Button from './Button'
import styles from './WatchedModal.module.css'

export default function WatchedModal({ isOpen, filmTitle, onConfirm, onSkip, loading = false }) {
  const [rating, setRating] = useState(0)
  const [review, setReview] = useState('')

  if (!isOpen) return null

  const reviewValid = review.trim().length === 0 || review.trim().length >= 10
  const canConfirm = reviewValid

  function handleConfirm() {
    if (!canConfirm) return
    onConfirm(rating, review.trim())
    setRating(0)
    setReview('')
  }

  function handleSkip() {
    setRating(0)
    setReview('')
    onSkip()
  }

  return (
    <div className={styles.overlay} onClick={handleSkip}>
      <div className={styles.card} onClick={(e) => e.stopPropagation()}>
        <div>
          <p className={styles.title}>Vous avez vu ce film ?</p>
          <p className={styles.subtitle}>{filmTitle}</p>
        </div>

        <div className={styles.ratingBlock}>
          <span className={styles.label}>Note (optionnelle)</span>
          <StarRating value={rating} onChange={setRating} />
        </div>

        <div className={styles.reviewBlock}>
          <span className={styles.label}>Votre avis (optionnel)</span>
          <textarea
            className={styles.textarea}
            placeholder="Partagez votre avis sur ce film… (min. 10 caractères si renseigné)"
            value={review}
            onChange={(e) => setReview(e.target.value)}
            rows={3}
          />
          {review.trim().length > 0 && !reviewValid && (
            <p className={styles.charHint}>{review.trim().length}/10 caractères minimum</p>
          )}
        </div>

        <div className={styles.actions}>
          <Button variant="ghost" onClick={handleSkip} disabled={loading}>
            Passer
          </Button>
          <Button variant="primary" onClick={handleConfirm} disabled={!canConfirm} loading={loading}>
            Confirmer
          </Button>
        </div>
      </div>
    </div>
  )
}
