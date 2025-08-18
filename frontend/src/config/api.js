// src/config/api.js
export const API_BASE_URL = "http://127.0.0.1:8000/api"; // Ganti sesuai domain backend kamu

export const ENDPOINTS = {
  login: `${API_BASE_URL}/login`,
  users: `${API_BASE_URL}/users`,
  itemMachines: `${API_BASE_URL}/item-machines`,
  activityTms: `${API_BASE_URL}/activity-tms-all`,
  addactivityTms: `${API_BASE_URL}/activity-tms`,
  activityTmsDetail: `${API_BASE_URL}/activity-tms`,
  updateActivityTms: `${API_BASE_URL}/activity-tms-update`,
  fawreport: `${API_BASE_URL}/faw-reports`,

  // LeakageReport endpoints
  leakageReports: `${API_BASE_URL}/leakage-reports`, // GET list & POST create
  getLeakageReport: (id) => `${API_BASE_URL}/leakage-reports/${id}`, // GET detail
  updateLeakageReport: (id) => `${API_BASE_URL}/leakage-reports/${id}`, // POST update
  deleteLeakageReport: (id) => `${API_BASE_URL}/leakage-reports/${id}`, // DELETE

  // schedule
  ACTIVITY_SUMMARY: `${API_BASE_URL}/activity-summary`,

  // Sparepart endpoints
  spareparts: `${API_BASE_URL}/spareparts`, // GET list & POST create
  getSparepart: (id) => `${API_BASE_URL}/spareparts/${id}`, // GET detail
  updateSparepart: (id) => `${API_BASE_URL}/spareparts/${id}`, // POST update
  deleteSparepart: (id) => `${API_BASE_URL}/spareparts/${id}`, // DELETE
};
