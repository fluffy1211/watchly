import axiosInstance from './axiosInstance'

export const reportComment = (commentId, reason) =>
  axiosInstance.post(`/comments/${commentId}/report`, { reason })

export const reportReview = (reviewId, reason) =>
  axiosInstance.post(`/reviews/${reviewId}/report`, { reason })

export const getReports = () =>
  axiosInstance.get('/admin/reports')

export const resolveReport = (reportId, action) =>
  axiosInstance.patch(`/admin/reports/${reportId}`, { action })
