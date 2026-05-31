import styles from './Spinner.module.css'

const sizeMap = {
  sm: 16,
  md: 24,
  lg: 40,
}

export default function Spinner({ size = 'md' }) {
  const px = sizeMap[size] || sizeMap.md

  return (
    <span
      className={styles.spinner}
      style={{ width: px, height: px }}
      role="status"
      aria-label="Chargement"
    />
  )
}
