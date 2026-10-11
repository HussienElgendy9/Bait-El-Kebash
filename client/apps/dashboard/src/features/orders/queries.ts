import { useMutation, useQueryClient } from "@tanstack/react-query"
import { updateOrderStatus } from "./api"

export const orderKeys = { all: ["orders"] as const }

export function useUpdateOrderStatus() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: updateOrderStatus,
    // Overview holds the per-status counts.
    onSuccess: () => Promise.all([orderKeys.all, ["overview"]].map((queryKey) => qc.invalidateQueries({ queryKey }))),
  })
}
