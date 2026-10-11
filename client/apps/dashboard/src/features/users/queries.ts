import { useMutation, useQueryClient } from "@tanstack/react-query"
import { updateRole } from "./api"

// A role change moves a user between the two lists.
export const userKeys = { customers: ["customers"] as const, admins: ["admins"] as const }

export function useUpdateRole() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: updateRole,
    onSuccess: () => Promise.all(Object.values(userKeys).map((queryKey) => qc.invalidateQueries({ queryKey }))),
  })
}
