import axiosInstance from './axiosInstance'

export const reportComment = (commentId, reason) =>
  axiosInstance.post(`/comments/${commentId}/report`, { reason })

export const getCommentReports = () =>
  axiosInstance.get('/admin/comment-reports')

export const resolveCommentReport = (reportId, action) =>
  axiosInstance.patch(`/admin/comment-reports/${reportId}`, { action })
