import Avatar from '../../components/ui/Avatar'

export default {
  title: 'Components/Avatar',
  component: Avatar,
  tags: ['autodocs'],
  argTypes: {
    size: { control: { type: 'range', min: 32, max: 160, step: 8 } },
  },
  args: {
    username: 'Gabriel',
    size: 96,
  },
}

export const Playground = {}

export const Initials = () => (
  <div style={{ display: 'flex', gap: 16 }}>
    <Avatar username="Gabriel" />
    <Avatar username="Marie" />
    <Avatar username="Julien" />
    <Avatar username="Sofia" />
    <Avatar username="Naomi" />
  </div>
)

const PLACEHOLDER_IMAGE =
  'data:image/svg+xml;base64,' +
  btoa(
    '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150"><rect width="150" height="150" fill="#5B7FD4"/></svg>'
  )

export const WithImage = () => (
  <Avatar username="Gabriel" avatarUrl={PLACEHOLDER_IMAGE} />
)

export const Sizes = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
    <Avatar username="Gabriel" size={32} />
    <Avatar username="Gabriel" size={64} />
    <Avatar username="Gabriel" size={96} />
    <Avatar username="Gabriel" size={128} />
  </div>
)
