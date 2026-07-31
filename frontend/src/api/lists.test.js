import axiosInstance from './axiosInstance'
import {
  getLists,
  getList,
  createList,
  updateList,
  deleteList,
  addFilmToList,
  removeFilmFromList,
  getComments,
  postComment,
  deleteComment,
} from './lists'

jest.mock('./axiosInstance')

describe('lists api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('getLists calls GET /lists without params when no query', () => {
    getLists()
    expect(axiosInstance.get).toHaveBeenCalledWith('/lists', { params: {} })
  })

  it('getLists passes q param when provided', () => {
    getLists('sci-fi')
    expect(axiosInstance.get).toHaveBeenCalledWith('/lists', { params: { q: 'sci-fi' } })
  })

  it('getList calls GET /lists/:id', () => {
    getList(7)
    expect(axiosInstance.get).toHaveBeenCalledWith('/lists/7')
  })

  it('createList posts title, description and visibility', () => {
    createList('My list', 'desc', 'PUBLIC')
    expect(axiosInstance.post).toHaveBeenCalledWith('/lists', {
      title: 'My list',
      description: 'desc',
      visibility: 'PUBLIC',
    })
  })

  it('updateList patches the list endpoint', () => {
    updateList(7, { title: 'Renamed' })
    expect(axiosInstance.patch).toHaveBeenCalledWith('/lists/7', { title: 'Renamed' })
  })

  it('deleteList deletes the list endpoint', () => {
    deleteList(7)
    expect(axiosInstance.delete).toHaveBeenCalledWith('/lists/7')
  })

  it('addFilmToList posts to the list films endpoint', () => {
    addFilmToList(7, 42)
    expect(axiosInstance.post).toHaveBeenCalledWith('/lists/7/films/42')
  })

  it('removeFilmFromList deletes the list films endpoint', () => {
    removeFilmFromList(7, 42)
    expect(axiosInstance.delete).toHaveBeenCalledWith('/lists/7/films/42')
  })

  it('getComments calls GET /lists/:id/comments', () => {
    getComments(7)
    expect(axiosInstance.get).toHaveBeenCalledWith('/lists/7/comments')
  })

  it('postComment posts content to the comments endpoint', () => {
    postComment(7, 'nice list')
    expect(axiosInstance.post).toHaveBeenCalledWith('/lists/7/comments', { content: 'nice list' })
  })

  it('deleteComment deletes the comment endpoint', () => {
    deleteComment(99)
    expect(axiosInstance.delete).toHaveBeenCalledWith('/comments/99')
  })
})
