import Button from '../../components/ui/Button'

export default {
  title: 'Components/Button',
  component: Button,
  tags: ['autodocs'],
  argTypes: {
    variant: { control: 'select', options: ['primary', 'secondary', 'danger', 'ghost'] },
    size: { control: 'select', options: ['sm', 'md', 'lg'] },
    loading: { control: 'boolean' },
    disabled: { control: 'boolean' },
  },
  args: {
    variant: 'primary',
    size: 'md',
    loading: false,
    disabled: false,
    children: 'Commencer gratuitement',
  },
}

export const Playground = {}

export const Variants = () => (
  <div style={{ display: 'flex', gap: 16 }}>
    <Button variant="primary">Primary</Button>
    <Button variant="secondary">Secondary</Button>
    <Button variant="danger">Danger</Button>
    <Button variant="ghost">Ghost</Button>
  </div>
)

export const Sizes = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
    <Button size="sm">Small</Button>
    <Button size="md">Medium</Button>
    <Button size="lg">Large</Button>
  </div>
)

export const Loading = () => (
  <div style={{ display: 'flex', gap: 16 }}>
    <Button variant="primary" loading>Confirmer</Button>
    <Button variant="secondary" loading>Confirmer</Button>
  </div>
)

export const Disabled = () => (
  <div style={{ display: 'flex', gap: 16 }}>
    <Button variant="primary" disabled>Confirmer</Button>
    <Button variant="ghost" disabled>Passer</Button>
  </div>
)
