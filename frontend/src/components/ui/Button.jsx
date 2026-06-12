import styles from './Button.module.css'
import Spinner from './Spinner'

export default function Button({
  variant = 'primary',
  size = 'md',
  loading = false,
  disabled = false,
  onClick,
  children,
  type = 'button',
  className,
  ...rest
}) {
  const classNames = [
    styles.button,
    styles[variant],
    styles[size],
    loading ? styles.loading : '',
    className,
  ].filter(Boolean).join(' ')

  return (
    <button
      className={classNames}
      onClick={onClick}
      disabled={disabled || loading}
      type={type}
      {...rest}
    >
      {loading && <Spinner size="sm" />}
      <span className={loading ? styles.hiddenText : ''}>{children}</span>
    </button>
  )
}
