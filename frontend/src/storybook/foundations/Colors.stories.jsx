import { ColorSwatch, TokenRow, TokenSection } from './TokenGrid'

export default {
  title: 'Foundations/Colors',
  tags: ['autodocs'],
}

export const Base = () => (
  <TokenSection title="Base colors">
    <TokenRow>
      <ColorSwatch name="--watchly-color-bg" />
      <ColorSwatch name="--watchly-color-surface" />
      <ColorSwatch name="--watchly-color-primary" />
      <ColorSwatch name="--watchly-color-success" />
      <ColorSwatch name="--watchly-color-danger" />
      <ColorSwatch name="--watchly-color-text" />
      <ColorSwatch name="--watchly-color-muted" />
      <ColorSwatch name="--watchly-color-on-solid" />
    </TokenRow>
  </TokenSection>
)

export const Surfaces = () => (
  <TokenSection title="Surfaces & borders">
    <TokenRow>
      <ColorSwatch name="--watchly-color-surface-raised" />
      <ColorSwatch name="--watchly-color-border" />
      <ColorSwatch name="--watchly-color-border-strong" />
      <ColorSwatch name="--watchly-color-border-soft" />
      <ColorSwatch name="--watchly-color-placeholder" />
      <ColorSwatch name="--watchly-color-field" />
      <ColorSwatch name="--watchly-color-field-hover" />
    </TokenRow>
  </TokenSection>
)

export const OverlayScale = () => (
  <TokenSection title="Overlay scale (hairlines & highlights)">
    <TokenRow>
      <ColorSwatch name="--watchly-color-overlay-faint" />
      <ColorSwatch name="--watchly-color-overlay-strong" />
      <ColorSwatch name="--watchly-color-overlay-heavy" />
    </TokenRow>
  </TokenSection>
)

export const PrimaryAlphaScale = () => (
  <TokenSection title="Primary alpha scale">
    <TokenRow>
      <ColorSwatch name="--watchly-color-primary-soft" />
      <ColorSwatch name="--watchly-color-primary-muted" />
      <ColorSwatch name="--watchly-color-primary-strong" />
    </TokenRow>
  </TokenSection>
)

export const SuccessAlphaScale = () => (
  <TokenSection title="Success alpha scale">
    <TokenRow>
      <ColorSwatch name="--watchly-color-success-soft" />
      <ColorSwatch name="--watchly-color-success-muted" />
      <ColorSwatch name="--watchly-color-success-strong" />
    </TokenRow>
  </TokenSection>
)

export const DangerAlphaScale = () => (
  <TokenSection title="Danger alpha scale">
    <TokenRow>
      <ColorSwatch name="--watchly-color-danger-soft" />
      <ColorSwatch name="--watchly-color-danger-muted" />
      <ColorSwatch name="--watchly-color-danger-strong" />
    </TokenRow>
  </TokenSection>
)

export const Scrims = () => (
  <TokenSection title="Scrims (modal backdrops)">
    <TokenRow>
      <ColorSwatch name="--watchly-scrim" />
      <ColorSwatch name="--watchly-scrim-heavy" />
    </TokenRow>
  </TokenSection>
)
