import { MemoryRouter } from 'react-router-dom'
import ListCard from '../../components/ListCard'

const LIST = {
  id: 1,
  title: 'Mes classiques',
  description: 'Les films que je revois chaque année.',
  visibility: 'PUBLIC',
  owner: { username: 'gabriel' },
  film_count: 12,
}

export default {
  title: 'Components/ListCard',
  component: ListCard,
  tags: ['autodocs'],
  decorators: [(Story) => <MemoryRouter><Story /></MemoryRouter>],
  args: {
    list: LIST,
  },
}

export const Playground = {}

export const Private = {
  args: { list: { ...LIST, visibility: 'PRIVATE' } },
}

export const NoDescription = {
  args: { list: { ...LIST, description: null } },
}
