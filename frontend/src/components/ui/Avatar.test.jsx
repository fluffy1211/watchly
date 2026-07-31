import { render, screen } from '@testing-library/react'
import Avatar from './Avatar'

describe('Avatar', () => {
  it('renders an img when avatarUrl is given', () => {
    render(<Avatar username="gabriel" avatarUrl="https://example.com/avatar.png" />)
    const img = screen.getByRole('img', { name: 'gabriel' })
    expect(img).toHaveAttribute('src', 'https://example.com/avatar.png')
  })

  it('renders the uppercased first letter of the username when no avatarUrl', () => {
    render(<Avatar username="gabriel" />)
    expect(screen.getByText('G')).toBeInTheDocument()
  })

  it('renders a fallback "?" when there is no username', () => {
    render(<Avatar username={null} />)
    expect(screen.getByText('?')).toBeInTheDocument()
  })

  it('applies the requested size as width and height', () => {
    render(<Avatar username="gabriel" size={48} />)
    expect(screen.getByText('G')).toHaveStyle({ width: '48px', height: '48px' })
  })
})
