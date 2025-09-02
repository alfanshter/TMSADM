// 👉 Redirects
export const redirects = [
  {
    path: '/',
    name: 'index',
    redirect: to => {
      const userData = useCookie('userData')?.value

      // Kalau userData kosong/null → langsung ke login
      if (!userData || !userData.user || !userData.user.role) {
        return { name: 'login', query: to.query }
      }

      const userRole = userData.user.role
      console.log("tester role", userRole)

      if (userRole === 'admin', 'team_leader', 'supervisor')
        return { name: 'root' }

      return { name: 'login', query: to.query }
    },
  },
]
