export default [
  { heading: "Dashboard" },

  {
    title: "Home",
    to: { name: "root" },
    icon: { icon: "ri-home-smile-2-line" },
  },
  {
    title: "Second page",
    to: { name: "second-page" },
    icon: { icon: "ri-file-text-line" },
  },

  {
    title: "User",
    to: { name: "user" },
    icon: { icon: "ri-user-line" },
  },

  {
    title: "TMS",
    to: { name: "tms" },
    icon: { icon: "ri-settings-2-line" },
  },

  {
    title: "Activity TMS",
    children: [
      { title: "List", to: "activitytms" },
      { title: "Add", to: "activitytms-form" },
    ],
    icon: { icon: "ri-pulse-line" },
  },

  {
    title: "FAW Report",
    children: [
      { title: "List", to: "fawreport" },
      { title: "Add", to: "fawreport-form" },
    ],
    icon: { icon: "ri-settings-2-line" },
  },

  {
    title: "Leakage Report",
    children: [
      { title: "List", to: "leakagereport" },
      { title: "Add", to: "leakagereport-form" },
    ],
    icon: { icon: "ri-alarm-warning-fill" },
  },

  {
    title: "Schedule",
    children: [{ title: "List", to: "schedule" }],
    icon: { icon: "ri-survey-line" },
  },
];
