import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import StarRating from './StarRating'

describe('StarRating', () => {
  it('renders as a read-only img role when no onChange is given', () => {
    render(<StarRating value={3} />)
    expect(screen.getByRole('img', { name: 'Note : 3 sur 5' })).toBeInTheDocument()
  })

  it('disables the star buttons when not interactive', () => {
    render(<StarRating value={3} />)
    expect(screen.getByRole('button', { name: '1 étoile' })).toBeDisabled()
  })

  it('renders as a radiogroup and enables buttons when interactive', () => {
    render(<StarRating value={2} onChange={() => {}} />)
    expect(screen.getByRole('radiogroup', { name: 'Note : 2 sur 5' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: '3 étoiles' })).toBeEnabled()
  })

  it('calls onChange with the clicked star value', async () => {
    const onChange = jest.fn()
    const user = userEvent.setup()
    render(<StarRating value={0} onChange={onChange} />)

    await user.click(screen.getByRole('button', { name: '4 étoiles' }))

    expect(onChange).toHaveBeenCalledWith(4)
  })

  it('does not call onChange when not interactive', async () => {
    const user = userEvent.setup()
    render(<StarRating value={2} />)

    await user.click(screen.getByRole('button', { name: '4 étoiles' }))

    expect(screen.getByRole('button', { name: '4 étoiles' })).toBeDisabled()
  })
})
