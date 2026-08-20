import { TokenSection } from './TokenGrid'

export default {
  title: 'Foundations/Typography',
  tags: ['autodocs'],
}

const SIZES = [
  '--watchly-text-xs',
  '--watchly-text-sm',
  '--watchly-text-base',
  '--watchly-text-lg',
  '--watchly-text-xl',
  '--watchly-text-2xl',
  '--watchly-text-3xl',
  '--watchly-text-4xl',
]

const ROLES = [
  '--watchly-display',
  '--watchly-title',
  '--watchly-subtitle',
  '--watchly-body',
  '--watchly-caption',
]

const WEIGHTS = [
  '--watchly-weight-regular',
  '--watchly-weight-medium',
  '--watchly-weight-bold',
]

export const Scale = () => (
  <TokenSection title="Size scale (--watchly-text-*)">
    {SIZES.map((name) => (
      <div key={name} style={{ display: 'flex', alignItems: 'baseline', gap: 16, marginBottom: 8 }}>
        <code style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-muted)', width: 160 }}>
          {name}
        </code>
        <span style={{ fontFamily: 'var(--watchly-font-display)', fontSize: `var(${name})`, color: 'var(--watchly-color-text)' }}>
          Watchly
        </span>
      </div>
    ))}
  </TokenSection>
)

export const SemanticRoles = () => (
  <TokenSection title="Semantic roles (built on the scale)">
    {ROLES.map((name) => (
      <div key={name} style={{ display: 'flex', alignItems: 'baseline', gap: 16, marginBottom: 8 }}>
        <code style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-muted)', width: 160 }}>
          {name}
        </code>
        <span style={{ fontFamily: 'var(--watchly-font-display)', fontSize: `var(${name})`, color: 'var(--watchly-color-text)' }}>
          Suivez chaque film
        </span>
      </div>
    ))}
  </TokenSection>
)

export const Families = () => (
  <TokenSection title="Font families">
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <p style={{ fontFamily: 'var(--watchly-font-display)', fontSize: 'var(--watchly-text-xl)', color: 'var(--watchly-color-text)' }}>
        --watchly-font-display — Plus Jakarta Sans
      </p>
      <p style={{ fontFamily: 'var(--watchly-font-body)', fontSize: 'var(--watchly-text-base)', color: 'var(--watchly-color-text)' }}>
        --watchly-font-body — Plus Jakarta Sans
      </p>
      <p style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-base)', color: 'var(--watchly-color-text)' }}>
        --watchly-font-mono — IBM Plex Mono
      </p>
    </div>
  </TokenSection>
)

export const Weights = () => (
  <TokenSection title="Font weights">
    {WEIGHTS.map((name) => (
      <div key={name} style={{ display: 'flex', alignItems: 'baseline', gap: 16, marginBottom: 8 }}>
        <code style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-muted)', width: 200 }}>
          {name}
        </code>
        <span style={{ fontFamily: 'var(--watchly-font-body)', fontSize: 'var(--watchly-text-lg)', fontWeight: `var(${name})`, color: 'var(--watchly-color-text)' }}>
          Watchly
        </span>
      </div>
    ))}
  </TokenSection>
)
