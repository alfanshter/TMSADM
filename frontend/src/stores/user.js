import { defineStore } from "pinia"
import Cookies from "js-cookie"

export const useUserStore = defineStore("user", {
  state: () => ({
    user: getUserFromCookies(), // 🔥 simpan object user lengkap
  }),

  getters: {
    role: (state) => state.user?.role || null,
    name: (state) => state.user?.name || null,
  },

  actions: {
    setUser(userData) {
      // simpan ke cookies
      Cookies.set("userData", JSON.stringify(userData))
      this.user = userData
    },
    logout() {
      Cookies.remove("userData")
      this.user = null
    },
  },
})

function getUserFromCookies() {
  const userData = Cookies.get("userData")
    ? JSON.parse(Cookies.get("userData"))
    : null
  return userData?.user || userData // fallback kalau API returnnya beda
}