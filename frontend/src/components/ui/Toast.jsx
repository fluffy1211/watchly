import styles from './Toast.module.css'

const icons = {
  success: '✓',
  error: '✕',
  info: 'ℹ',
}

function Toast({ toast, onClose }) {
  return (
    <div className={`${styles.toast} ${styles[toast.variant]}`} role="alert">
      <span className={styles.icon}>{icons[toast.variant]}</span>
      <span className={styles.message}>{toast.message}</span>
      <button className={styles.close} onClick={() => onClose(toast.id)} aria-label="Fermer">
        ✕
      </button>
    </div>
  )
}

export default function ToastContainer({ toasts, removeToast }) {
  if (!toasts.length) return null

  return (
    <div className={styles.container}>
      {toasts.map((toast) => (
        <Toast key={toast.id} toast={toast} onClose={removeToast} />
      ))}
    </div>
  )
}
