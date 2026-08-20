function resolvedValue(varName) {
  if (typeof window === 'undefined') return ''
  return getComputedStyle(document.documentElement).getPropertyValue(varName).trim()
}

export function ColorSwatch({ name }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 160 }}>
      <div
        style={{
          height: 64,
          borderRadius: 'var(--watchly-radius-md)',
          border: '1px solid var(--watchly-color-border)',
          background: `var(${name})`,
          backgroundImage:
            'repeating-conic-gradient(#00000022 0% 25%, transparent 0% 50%)',
          backgroundSize: '12px 12px',
        }}
      >
        <div
          style={{
            height: '100%',
            borderRadius: 'var(--watchly-radius-md)',
            background: `var(${name})`,
          }}
        />
      </div>
      <code style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-text)' }}>
        {name}
      </code>
      <span style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-muted)' }}>
        {resolvedValue(name)}
      </span>
    </div>
  )
}

export function TokenRow({ children }) {
  return (
    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 24 }}>{children}</div>
  )
}

export function TokenSection({ title, children }) {
  return (
    <section style={{ marginBottom: 32 }}>
      <h3
        style={{
          fontFamily: 'var(--watchly-font-display)',
          fontSize: 'var(--watchly-text-lg)',
          color: 'var(--watchly-color-text)',
          marginBottom: 16,
        }}
      >
        {title}
      </h3>
      {children}
    </section>
  )
}
