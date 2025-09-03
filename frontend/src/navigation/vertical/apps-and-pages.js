import { computed } from "vue"
import { storeToRefs } from "pinia"
import { useUserStore } from "@/stores/user"

const items = [
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
    role: ["admin"],
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
      { title: "Add", to: "activitytms-form",role: ["admin","team_leader"] },
    ],
    icon: { icon: "ri-tools-line" },
  },

  {
    title: "FAW Report",
    children: [
      { title: "List", to: "fawreport" },
      { title: "Add", to: "fawreport-form",role: ["admin","team_leader"] },
    ],
    icon: { icon: "ri-customer-service-2-line" },
  },

  {
    title: "Leakage Report",
    children: [
      { title: "List", to: "leakagereport" },
      { title: "Add", to: "leakagereport-form", role: ["admin","team_leader"] },
    ],
    icon: { icon: "ri-alarm-warning-fill" },
  },

  {
    title: "Schedule",
    children: [{ title: "List", to: "schedule" }],
    icon: { icon: "ri-survey-line" },
  },

  {
    title: "Sparepart",
    to: { name: "sparepart" },
    icon: { icon: "ri-hammer-line" },
  },
  {
    title: "Pica",
    to: { name: "pica" },
    icon: { icon: "ri-send-plane-line" },
  },
]

export function useNavItems() {
  const userStore = useUserStore()
  const { user } = storeToRefs(userStore) // reactive user dari Pinia

  return computed(() => {
    const role = user.value.role

    return items
      .map(i => {
        if (i.children) {
          return {
            ...i,
            children: i.children.filter(
              c => !c.role || c.role.includes(role) // filter child
            ),
          }
        }
        return i
      })
      .filter(i => !i.role || i.role.includes(role)) // filter parent
  })
}