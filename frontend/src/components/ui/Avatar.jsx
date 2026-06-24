import styles from './Avatar.module.css'

const GRADIENTS = [
  'linear-gradient(135deg, #E8B86D, #D4A054)',
  'linear-gradient(135deg, #7C9EE8, #5B7FD4)',
  'linear-gradient(135deg, #74D4A8, #4ABDA0)',
  'linear-gradient(135deg, #E87C9E, #D45B7F)',
  'linear-gradient(135deg, #B87CE8, #9A5BD4)',
]

function getGradient(username) {
  const code = username ? username.charCodeAt(0) : 0
  return GRADIENTS[code % GRADIENTS.length]
}

export default function Avatar({ username, avatarUrl, size = 96 }) {
  const dim = { width: size, height: size }

  if (avatarUrl) {
    return (
      <img
        className={styles.avatar}
        src={avatarUrl}
        alt={username}
        style={dim}
      />
    )
  }

  return (
    <div
      className={styles.avatar}
      style={{ ...dim, background: getGradient(username), fontSize: size * 0.4 }}
    >
      {username?.[0]?.toUpperCase() ?? '?'}
    </div>
  )
}
