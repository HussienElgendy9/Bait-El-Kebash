import { queryOptions, useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { getMe, login, logout, register } from "./api"
import type { AuthResponse } from "./types"

export const authKeys = { me: ["me"] as const }

export const meOptions = () =>
  queryOptions({
    queryKey: authKeys.me,
    queryFn: getMe,
  })

export const useMe = () => useQuery(meOptions())

function useAuthSuccess() {
  const qc = useQueryClient()
  return ({ user }: AuthResponse) => {
    qc.setQueryData(authKeys.me, user)
  }
}

export const useLogin = () => useMutation({ mutationFn: login, onSuccess: useAuthSuccess() })
export const useRegister = () => useMutation({ mutationFn: register, onSuccess: useAuthSuccess() })

export function useLogout() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: logout,
    onSettled: () => qc.clear(),
  })
}
