import { TokenSection } from './TokenGrid'

export default {
  title: 'Foundations/Spacing',
  tags: ['autodocs'],
}

const SPACES = [
  '--watchly-space-1',
  '--watchly-space-2',
  '--watchly-space-3',
  '--watchly-space-4',
  '--watchly-space-6',
  '--watchly-space-8',
  '--watchly-space-12',
  '--watchly-space-16',
]

export const Scale = () => (
  <TokenSection title="Spacing scale">
    {SPACES.map((name) => (
      <div key={name} style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 8 }}>
        <code style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-muted)', width: 160 }}>
          {name}
        </code>
        <div style={{ width: `var(${name})`, height: 16, background: 'var(--watchly-color-primary)', borderRadius: 'var(--watchly-radius-sm)' }} />
      </div>
    ))}
  </TokenSection>
)
