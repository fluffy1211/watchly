import FilmCard from '../../components/ui/FilmCard'

// poster_path is left null: FilmCard prefixes it with the TMDB CDN URL,
// so Storybook shows the 🎬 placeholder state instead of hitting the network.
const FILM = {
  id: 1,
  title: 'Le Fabuleux Destin d’Amélie Poulain',
  release_date: '2001-04-25',
  poster_path: null,
}

export default {
  title: 'Components/FilmCard',
  component: FilmCard,
  tags: ['autodocs'],
  args: {
    film: FILM,
  },
  decorators: [(Story) => <div style={{ width: 200 }}><Story /></div>],
}

export const Playground = {}

export const Grid = () => (
  <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 200px)', gap: 24 }}>
    <FilmCard film={FILM} />
    <FilmCard film={{ ...FILM, id: 2, title: 'Interstellar', release_date: '2014-11-05' }} />
    <FilmCard film={{ ...FILM, id: 3, title: 'Dune', release_date: '2021-09-15' }} />
  </div>
)
