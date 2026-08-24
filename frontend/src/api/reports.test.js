import axiosInstance from './axiosInstance'
import {
  reportComment, getCommentReports, resolveCommentReport,
  reportReview, getReviewReports, resolveReviewReport,
} from './reports'

jest.mock('./axiosInstance')

describe('reports api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('reportComment posts a reason to the comment report endpoint', () => {
    reportComment(3, 'spam')
    expect(axiosInstance.post).toHaveBeenCalledWith('/comments/3/report', { reason: 'spam' })
  })

  it('getCommentReports calls GET /admin/comment-reports', () => {
    getCommentReports()
    expect(axiosInstance.get).toHaveBeenCalledWith('/admin/comment-reports')
  })

  it('resolveCommentReport patches the report with an action', () => {
    resolveCommentReport(9, 'DISMISS')
    expect(axiosInstance.patch).toHaveBeenCalledWith('/admin/comment-reports/9', { action: 'DISMISS' })
  })

  it('reportReview posts a reason to the review report endpoint', () => {
    reportReview(3, 'spam')
    expect(axiosInstance.post).toHaveBeenCalledWith('/reviews/3/report', { reason: 'spam' })
  })

  it('getReviewReports calls GET /admin/review-reports', () => {
    getReviewReports()
    expect(axiosInstance.get).toHaveBeenCalledWith('/admin/review-reports')
  })

  it('resolveReviewReport patches the report with an action', () => {
    resolveReviewReport(9, 'DISMISS')
    expect(axiosInstance.patch).toHaveBeenCalledWith('/admin/review-reports/9', { action: 'DISMISS' })
  })
})
