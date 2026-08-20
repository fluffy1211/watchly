import WatchedModal from '../../components/ui/WatchedModal'

export default {
  title: 'Components/WatchedModal',
  component: WatchedModal,
  tags: ['autodocs'],
  // .overlay is position: fixed + inset: 0 (pinned to the viewport). A transformed
  // ancestor turns that into a containing block, so the modal stays inside the
  // story preview instead of centering on the real page and clipping there.
  decorators: [
    (Story) => (
      <div style={{ position: 'relative', height: 480, transform: 'translateZ(0)' }}>
        <Story />
      </div>
    ),
  ],
  args: {
    isOpen: true,
    filmTitle: 'Inception',
    loading: false,
    onConfirm: () => {},
    onSkip: () => {},
  },
}

export const Playground = {}

export const Loading = {
  args: { loading: true },
}
