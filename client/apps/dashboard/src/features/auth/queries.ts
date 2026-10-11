import { queryOptions, useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { ApiError, TOKEN_KEY } from "@/shared/api/client"
import { getMe, login, logout } from "./api"
import type { LoginInput } from "./schemas"
import type { User } from "./types"

export const authKeys = { me: ["me"] as const }

export const hasToken = () => !!localStorage.getItem(TOKEN_KEY)
export const isAdmin = (u?: User | null) => u?.role === "admin" || u?.role === "super_admin"

export const meOptions = () =>
  queryOptions({
    queryKey: authKeys.me,
    queryFn: () => (hasToken() ? getMe() : null),
    staleTime: Infinity,
  })

export const useMe = () => useQuery(meOptions())

/** Login doesn't return `role`, so fetch `/user` with the new token and reject non-admins. */
export function useLogin() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (input: LoginInput) => {
      const { token } = await login(input)
      localStorage.setItem(TOKEN_KEY, token)
      const me = await getMe()
      if (!isAdmin(me)) {
        await logout().catch(() => {})
        localStorage.removeItem(TOKEN_KEY)
        throw new ApiError(403, "auth.notAdmin")
      }
      return me
    },
    onSuccess: (me) => qc.setQueryData(authKeys.me, me),
    meta: { inline: true }, // LoginForm renders its own errors

  })
}

export function useLogout() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: logout,
    onSettled: () => {
      localStorage.removeItem(TOKEN_KEY)
      qc.clear()
    },
  })
}
