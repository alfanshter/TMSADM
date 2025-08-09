// src/config/api.js
export const API_BASE_URL = "http://127.0.0.1:8000/api"; // Ganti sesuai domain backend kamu

export const ENDPOINTS = {
  login: `${API_BASE_URL}/login`,
  users: `${API_BASE_URL}/users`,
  itemMachines: `${API_BASE_URL}/item-machines`,
  activityTms: `${API_BASE_URL}/activity-tms-all`,
  addactivityTms: `${API_BASE_URL}/activity-tms`,
  fawreport: `${API_BASE_URL}/faw-reports`,
};
