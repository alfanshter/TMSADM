export default [
  { heading: "Apps & Pages" },

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
    title: "ItemMachine",
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
];
