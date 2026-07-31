import axiosInstance from './axiosInstance'
import { search, getById, getPopular, getGenres, discoverByGenre } from './films'

jest.mock('./axiosInstance')

describe('films api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('search calls GET /films/search with query and default page', () => {
    search('matrix')
    expect(axiosInstance.get).toHaveBeenCalledWith('/films/search', { params: { q: 'matrix', page: 1 } })
  })

  it('search passes an explicit page', () => {
    search('matrix', 3)
    expect(axiosInstance.get).toHaveBeenCalledWith('/films/search', { params: { q: 'matrix', page: 3 } })
  })

  it('getById calls GET /films/:id', () => {
    getById(42)
    expect(axiosInstance.get).toHaveBeenCalledWith('/films/42')
  })

  it('getPopular calls GET /films/popular with default page', () => {
    getPopular()
    expect(axiosInstance.get).toHaveBeenCalledWith('/films/popular', { params: { page: 1 } })
  })

  it('getGenres calls GET /films/genres', () => {
    getGenres()
    expect(axiosInstance.get).toHaveBeenCalledWith('/films/genres')
  })

  it('discoverByGenre calls GET /films/discover with genre_id and default page', () => {
    discoverByGenre(28)
    expect(axiosInstance.get).toHaveBeenCalledWith('/films/discover', { params: { genre_id: 28, page: 1 } })
  })
})
