import { TokenSection, TokenRow } from './TokenGrid'

export default {
  title: 'Foundations/Elevation',
  tags: ['autodocs'],
}

const SHADOWS = ['--watchly-shadow-sm', '--watchly-shadow-md', '--watchly-shadow-lg']

export const Shadows = () => (
  <TokenSection title="Shadows">
    <TokenRow>
      {SHADOWS.map((name) => (
        <div key={name} style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 140 }}>
          <div
            style={{
              width: 120,
              height: 80,
              borderRadius: 'var(--watchly-radius-md)',
              background: 'var(--watchly-color-surface)',
              boxShadow: `var(${name})`,
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

export const Scrims = () => (
  <TokenSection title="Scrims (over an image)">
    <TokenRow>
      {['--watchly-scrim', '--watchly-scrim-heavy'].map((name) => (
        <div key={name} style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 140 }}>
          <div
            style={{
              width: 120,
              height: 80,
              borderRadius: 'var(--watchly-radius-md)',
              background: 'linear-gradient(135deg, #E8B86D, #4E4D93)',
              position: 'relative',
            }}
          >
            <div style={{ position: 'absolute', inset: 0, background: `var(${name})`, borderRadius: 'var(--watchly-radius-md)' }} />
          </div>
          <code style={{ fontFamily: 'var(--watchly-font-mono)', fontSize: 'var(--watchly-text-xs)', color: 'var(--watchly-color-muted)' }}>
            {name}
          </code>
        </div>
      ))}
    </TokenRow>
  </TokenSection>
)
