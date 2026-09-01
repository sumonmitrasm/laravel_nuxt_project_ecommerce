export default defineNuxtRouteMiddleware(
  async (to) => {
    const {user,authLoaded,fetchUser,} = useAuth()

    if (!authLoaded.value) {
      await fetchUser()
    }

    if (!user.value) { 
        return navigateTo({
        path: '/login',
        query: {
          redirect: to.fullPath,
        },
      })
    }
  }
)