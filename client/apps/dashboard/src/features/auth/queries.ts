import { queryOptions, useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { ApiError } from "@/shared/api/client"
import { getMe, login, logout } from "./api"
import type { LoginInput } from "./schemas"
import type { User } from "./types"

export const authKeys = { me: ["me"] as const }

export const isAdmin = (u?: User | null) => u?.role === "admin" || u?.role === "super_admin"

export const meOptions = () =>
  queryOptions({
    queryKey: authKeys.me,
    queryFn: getMe,
    staleTime: Infinity,
  })

export const useMe = () => useQuery(meOptions())

/** Login opens a session for anyone; sign non-admins straight back out. */
export function useLogin() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (input: LoginInput) => {
      const { user: me } = await login(input) // includes `role` since the requester is now the user
      if (!isAdmin(me)) {
        await logout().catch(() => {})
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
    onSettled: () => qc.clear(),
  })
}
