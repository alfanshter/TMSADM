export default [
  { heading: "Apps & Pages" },

  {
    title: "Home",
    to: { name: "root" },
    icon: { icon: "ri-home-smile-2-line" },
  },

  {
    title: "User",
    to: { name: "user" },
    icon: { icon: "ri-user-line" },
  },

  {
    title: "Item Machine",
    to: { name: "tms" },
    icon: { icon: "ri-settings-2-line" },
  },

  {
    title: "Activity TMS",
    children: [
      { title: "List", to: "activitytms" },
      { title: "Add", to: "activitytms-form" },
    ],
    icon: { icon: "ri-tools-line" },
  },

  {
    title: "FAW Report",
    to: { name: "fawreport" },
    icon: { icon: "ri-customer-service-2-line" },
  },

  {
    title: "Leakage Report",
    to: { name: "leakagereport" },
    icon: { icon: "ri-alarm-warning-fill" },
  },

  {
    title: "Schedule",
    children: [{ title: "List", to: "schedule" }],
    icon: { icon: "ri-survey-line" },
  },
];
