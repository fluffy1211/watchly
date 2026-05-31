import styles from './Badge.module.css'

const variantConfig = {
  watchlist: { label: 'Watchlist', className: 'watchlist' },
  watched:   { label: 'Vu',        className: 'watched' },
  favorite:  { label: '★ Favori',  className: 'favorite' },
}

export default function Badge({ variant = 'watchlist', children }) {
  const config = variantConfig[variant] || variantConfig.watchlist

  return (
    <span className={`${styles.badge} ${styles[config.className]}`}>
      {children || config.label}
    </span>
  )
}
