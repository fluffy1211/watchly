import axiosInstance from './axiosInstance'

export const reportComment = (commentId, reason) =>
  axiosInstance.post(`/comments/${commentId}/report`, { reason })

export const getCommentReports = () =>
  axiosInstance.get('/admin/comment-reports')

export const resolveCommentReport = (reportId, action) =>
  axiosInstance.patch(`/admin/comment-reports/${reportId}`, { action })

export const reportReview = (reviewId, reason) =>
  axiosInstance.post(`/reviews/${reviewId}/report`, { reason })

export const getReviewReports = () =>
  axiosInstance.get('/admin/review-reports')

export const resolveReviewReport = (reportId, action) =>
  axiosInstance.patch(`/admin/review-reports/${reportId}`, { action })
