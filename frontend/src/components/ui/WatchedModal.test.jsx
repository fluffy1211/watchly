import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import WatchedModal from './WatchedModal'

describe('WatchedModal', () => {
  it('renders nothing when isOpen is false', () => {
    const { container } = render(
      <WatchedModal isOpen={false} filmTitle="Matrix" onConfirm={() => {}} onSkip={() => {}} />
    )
    expect(container).toBeEmptyDOMElement()
  })

  it('renders the film title when open', () => {
    render(<WatchedModal isOpen filmTitle="Matrix" onConfirm={() => {}} onSkip={() => {}} />)
    expect(screen.getByText('Matrix')).toBeInTheDocument()
  })

  it('confirm button is enabled with an empty review', () => {
    render(<WatchedModal isOpen filmTitle="Matrix" onConfirm={() => {}} onSkip={() => {}} />)
    expect(screen.getByRole('button', { name: 'Confirmer' })).toBeEnabled()
  })

  it('disables confirm and shows a hint when the review is too short', async () => {
    const user = userEvent.setup()
    render(<WatchedModal isOpen filmTitle="Matrix" onConfirm={() => {}} onSkip={() => {}} />)

    await user.type(screen.getByPlaceholderText(/Partagez votre avis/), 'short')

    expect(screen.getByRole('button', { name: 'Confirmer' })).toBeDisabled()
    expect(screen.getByText('5/10 caractères minimum')).toBeInTheDocument()
  })

  it('calls onConfirm with rating and trimmed review, then resets fields', async () => {
    const onConfirm = jest.fn()
    const user = userEvent.setup()
    render(<WatchedModal isOpen filmTitle="Matrix" onConfirm={onConfirm} onSkip={() => {}} />)

    await user.type(screen.getByPlaceholderText(/Partagez votre avis/), '  Great movie!  ')
    await user.click(screen.getByRole('button', { name: '5 étoiles' }))
    await user.click(screen.getByRole('button', { name: 'Confirmer' }))

    expect(onConfirm).toHaveBeenCalledWith(5, 'Great movie!')
  })

  it('calls onSkip and resets fields when skip is clicked', async () => {
    const onSkip = jest.fn()
    const user = userEvent.setup()
    render(<WatchedModal isOpen filmTitle="Matrix" onConfirm={() => {}} onSkip={onSkip} />)

    await user.click(screen.getByRole('button', { name: 'Passer' }))

    expect(onSkip).toHaveBeenCalledTimes(1)
  })

  it('disables skip while loading', () => {
    render(<WatchedModal isOpen filmTitle="Matrix" onConfirm={() => {}} onSkip={() => {}} loading />)
    expect(screen.getByRole('button', { name: 'Passer' })).toBeDisabled()
  })
})
