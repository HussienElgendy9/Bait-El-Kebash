import { queryOptions, useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { TOKEN_KEY } from "@/shared/api/client"
import { getMe, login, logout, register } from "./api"
import type { AuthResponse } from "./types"

export const authKeys = { me: ["me"] as const }

export const hasToken = () => !!localStorage.getItem(TOKEN_KEY)

export const meOptions = () =>
  queryOptions({
    queryKey: authKeys.me,
    queryFn: () => (hasToken() ? getMe() : null),
  })

export const useMe = () => useQuery(meOptions())

function useAuthSuccess() {
  const qc = useQueryClient()
  return ({ token, user }: AuthResponse) => {
    localStorage.setItem(TOKEN_KEY, token)
    qc.setQueryData(authKeys.me, user)
  }
}

export const useLogin = () => useMutation({ mutationFn: login, onSuccess: useAuthSuccess() })
export const useRegister = () => useMutation({ mutationFn: register, onSuccess: useAuthSuccess() })

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
