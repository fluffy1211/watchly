import { TokenSection, TokenRow } from './TokenGrid'

export default {
  title: 'Foundations/Radius',
  tags: ['autodocs'],
}

const RADII = [
  '--watchly-radius-sm',
  '--watchly-radius-md',
  '--watchly-radius-lg',
  '--watchly-radius-full',
]

export const Scale = () => (
  <TokenSection title="Border radius">
    <TokenRow>
      {RADII.map((name) => (
        <div key={name} style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 120 }}>
          <div
            style={{
              width: 96,
              height: 96,
              borderRadius: `var(${name})`,
              background: 'var(--watchly-color-surface-raised)',
              border: '1px solid var(--watchly-color-border)',
            }}
          />
          <code style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-muted)' }}>
            {name}
          </code>
        </div>
      ))}
    </TokenRow>
  </TokenSection>
)
