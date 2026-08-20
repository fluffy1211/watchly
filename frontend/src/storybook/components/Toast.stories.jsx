import ToastContainer from '../../components/ui/Toast'

export default {
  title: 'Components/Toast',
  component: ToastContainer,
  tags: ['autodocs'],
  // .container is position: fixed (pinned to the viewport). A transformed
  // ancestor turns that into a containing block, so the toast stays inside
  // the story preview instead of escaping to the real page's bottom-right corner.
  decorators: [
    (Story) => (
      <div style={{ position: 'relative', height: 140, transform: 'translateZ(0)' }}>
        <Story />
      </div>
    ),
  ],
}

export const Success = () => (
  <ToastContainer
    toasts={[{ id: 1, variant: 'success', message: 'Film ajouté à votre collection' }]}
    removeToast={() => {}}
  />
)

export const Error = () => (
  <ToastContainer
    toasts={[{ id: 1, variant: 'error', message: 'Une erreur est survenue' }]}
    removeToast={() => {}}
  />
)

export const Info = () => (
  <ToastContainer
    toasts={[{ id: 1, variant: 'info', message: 'Votre profil a été mis à jour' }]}
    removeToast={() => {}}
  />
)

export const Stacked = () => (
  <ToastContainer
    toasts={[
      { id: 1, variant: 'success', message: 'Film ajouté à votre collection' },
      { id: 2, variant: 'info', message: 'Votre profil a été mis à jour' },
      { id: 3, variant: 'error', message: 'Une erreur est survenue' },
    ]}
    removeToast={() => {}}
  />
)
