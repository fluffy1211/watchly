import { useState } from 'react'
import styles from './StarRating.module.css'

export default function StarRating({ value = 0, onChange = null }) {
  const [hovered, setHovered] = useState(0)
  const interactive = typeof onChange === 'function'

  return (
    <div
      className={styles.stars}
      onMouseLeave={() => interactive && setHovered(0)}
      role={interactive ? 'radiogroup' : 'img'}
      aria-label={`Note : ${value || 0} sur 5`}
    >
      {[1, 2, 3, 4, 5].map((star) => {
        const filled = hovered ? star <= hovered : star <= value

        return (
          <button
            key={star}
            type="button"
            className={`${styles.star} ${filled ? styles.filled : styles.empty}`}
            onClick={() => interactive && onChange(star)}
            onMouseEnter={() => interactive && setHovered(star)}
            disabled={!interactive}
            aria-label={`${star} étoile${star > 1 ? 's' : ''}`}
          >
            ★
          </button>
        )
      })}
    </div>
  )
}
