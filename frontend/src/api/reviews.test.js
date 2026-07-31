import axiosInstance from './axiosInstance'
import { getReviews, putReview, deleteReview } from './reviews'

jest.mock('./axiosInstance')

describe('reviews api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('getReviews calls GET /films/:filmId/reviews', () => {
    getReviews(42)
    expect(axiosInstance.get).toHaveBeenCalledWith('/films/42/reviews')
  })

  it('putReview puts content to the review endpoint', () => {
    putReview(42, 'great film')
    expect(axiosInstance.put).toHaveBeenCalledWith('/films/42/review', { content: 'great film' })
  })

  it('deleteReview deletes the review endpoint', () => {
    deleteReview(42)
    expect(axiosInstance.delete).toHaveBeenCalledWith('/films/42/review')
  })
})
