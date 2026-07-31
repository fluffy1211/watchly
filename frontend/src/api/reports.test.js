import axiosInstance from './axiosInstance'
import { reportComment, getCommentReports, resolveCommentReport } from './reports'

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
})
