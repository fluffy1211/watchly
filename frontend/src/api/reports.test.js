import axiosInstance from './axiosInstance'
import { reportComment, reportReview, getReports, resolveReport } from './reports'

jest.mock('./axiosInstance')

describe('reports api', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  it('reportComment posts a reason to the comment report endpoint', () => {
    reportComment(3, 'spam')
    expect(axiosInstance.post).toHaveBeenCalledWith('/comments/3/report', { reason: 'spam' })
  })

  it('reportReview posts a reason to the review report endpoint', () => {
    reportReview(3, 'spam')
    expect(axiosInstance.post).toHaveBeenCalledWith('/reviews/3/report', { reason: 'spam' })
  })

  it('getReports calls GET /admin/reports', () => {
    getReports()
    expect(axiosInstance.get).toHaveBeenCalledWith('/admin/reports')
  })

  it('resolveReport patches the report with an action', () => {
    resolveReport(9, 'delete')
    expect(axiosInstance.patch).toHaveBeenCalledWith('/admin/reports/9', { action: 'delete' })
  })
})
