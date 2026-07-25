import axiosInstance from './axiosInstance'
import {
  getCollection,
  addFilm,
  updateStatus,
  toggleFavorite,
  updateRating,
  removeFilm,
} from './collection'

jest.mock('./axiosInstance')

describe('collection api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('getCollection calls GET /collection', () => {
    getCollection()
    expect(axiosInstance.get).toHaveBeenCalledWith('/collection')
  })

  it('addFilm posts tmdb_id as an integer, without status when omitted', () => {
    addFilm('123')
    expect(axiosInstance.post).toHaveBeenCalledWith('/collection/add', { tmdb_id: 123 })
  })

  it('addFilm includes status when provided', () => {
    addFilm('123', 'WATCHED')
    expect(axiosInstance.post).toHaveBeenCalledWith('/collection/add', {
      tmdb_id: 123,
      status: 'WATCHED',
    })
  })

  it('updateStatus patches the status endpoint', () => {
    updateStatus(5, 'WATCHED')
    expect(axiosInstance.patch).toHaveBeenCalledWith('/collection/5/status', { status: 'WATCHED' })
  })

  it('toggleFavorite patches the favorite endpoint', () => {
    toggleFavorite(5, true)
    expect(axiosInstance.patch).toHaveBeenCalledWith('/collection/5/favorite', { is_favorite: true })
  })

  it('updateRating patches the rating endpoint', () => {
    updateRating(5, 4)
    expect(axiosInstance.patch).toHaveBeenCalledWith('/collection/5/rating', { rating: 4 })
  })

  it('removeFilm deletes the collection entry', () => {
    removeFilm(5)
    expect(axiosInstance.delete).toHaveBeenCalledWith('/collection/5')
  })
})
