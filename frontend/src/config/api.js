// src/config/api.js
export const API_BASE_URL = "http://127.0.0.1:8000/api"; // Ganti sesuai domain backend kamu

export const ENDPOINTS = {
  login: `${API_BASE_URL}/login`,
  users: `${API_BASE_URL}/users`,
  itemMachines: `${API_BASE_URL}/item-machines`,
  spareparts: `${API_BASE_URL}/spareparts`,
  activityTms: `${API_BASE_URL}/activity-tms-all`,
  detailActivityTms: (id) => `${API_BASE_URL}/activity-tms/${id}`, // POST update
  addactivityTms: `${API_BASE_URL}/activity-tms`,
  activityTmsDetail: `${API_BASE_URL}/activity-tms`,
  updateActivityTms: `${API_BASE_URL}/activity-tms-update`,
  getActivityByScheduleList: `${API_BASE_URL}/getActivityByScheduleList`,
  
  // FAWReport endpoints
  fawreport: `${API_BASE_URL}/faw-reports`,
  fawReportDetail: (id) => `${API_BASE_URL}/faw-reports/${id}`,
  fawReportUpdate: `${API_BASE_URL}/faw-reports-update`,

  // LeakageReport endpoints
  leakageReports: `${API_BASE_URL}/leakage-reports`, // GET list & POST create
  getLeakageReport: (id) => `${API_BASE_URL}/leakage-reports/${id}`, // GET detail
  updateLeakageReport: (id) => `${API_BASE_URL}/leakage-reports/${id}`, // POST update
  deleteLeakageReport: (id) => `${API_BASE_URL}/leakage-reports/${id}`, // DELETE

  // schedule
  ACTIVITY_SUMMARY: `${API_BASE_URL}/activity-summary`,
  exportSchedule: (year, month) => `${API_BASE_URL}/export-pm-schedule?month=${year}-${month}`,

  // Sparepart endpoints
  getSparepart: (id) => `${API_BASE_URL}/spareparts/${id}`, // GET detail
  updateSparepart: (id) => `${API_BASE_URL}/spareparts/${id}`, // POST update
  deleteSparepart: (id) => `${API_BASE_URL}/spareparts/${id}`, // DELETE
  //tms sparepart
  deleteTmsSparepart: (id) => `${API_BASE_URL}/tmssparepart/${id}`, // DELETE
  addTmsSparepart: `${API_BASE_URL}/tmssparepart`, // tambah

  // export excel
  exportSpareparts: (year) => `${API_BASE_URL}/spareparts/export?year=${year}`,

  // PICA endpoints
  picas: `${API_BASE_URL}/picas`, // GET list & POST create
  getPica: (id) => `${API_BASE_URL}/picas/${id}`, // GET detail
  updatePica: (id) => `${API_BASE_URL}/picas/${id}`, // PUT update
  deletePica: (id) => `${API_BASE_URL}/picas/${id}`, // DELETE

  //dashboard
  dashboardStatistics: `${API_BASE_URL}/dashboard-statistics`,


};
