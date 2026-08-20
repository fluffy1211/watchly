import { useState } from 'react'
import StarRating from '../../components/ui/StarRating'

export default {
  title: 'Components/StarRating',
  component: StarRating,
  tags: ['autodocs'],
  argTypes: {
    value: { control: { type: 'range', min: 0, max: 5, step: 1 } },
  },
  args: {
    value: 3,
  },
}

export const Playground = {}

export const ReadOnly = () => <StarRating value={4} />

export const Interactive = () => {
  const [value, setValue] = useState(0)
  return <StarRating value={value} onChange={setValue} />
}
