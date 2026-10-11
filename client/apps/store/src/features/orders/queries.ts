import {
  infiniteQueryOptions,
  queryOptions,
  useInfiniteQuery,
  useMutation,
  useQuery,
  useQueryClient,
} from "@tanstack/react-query"
import { createOrder, getOrder, getOrders } from "./api"

export const orderKeys = {
  all: ["orders"] as const,
  list: () => [...orderKeys.all, "list"] as const,
  detail: (id: number) => [...orderKeys.all, "detail", id] as const,
}

export const orderListOptions = () =>
  infiniteQueryOptions({
    queryKey: orderKeys.list(),
    queryFn: ({ pageParam }) => getOrders(pageParam),
    initialPageParam: 1,
    getNextPageParam: (last) =>
      last.meta.current_page < last.meta.last_page ? last.meta.current_page + 1 : undefined,
    select: (data) => data.pages.flatMap((p) => p.data),
  })

export const orderOptions = (id: number) =>
  queryOptions({ queryKey: orderKeys.detail(id), queryFn: () => getOrder(id) })

export const useOrders = () => useInfiniteQuery(orderListOptions())
export const useOrder = (id: number) => useQuery(orderOptions(id))

export function useCreateOrder() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: createOrder,
    onSuccess: (order) => {
      qc.setQueryData(orderKeys.detail(order.id), order)
      return qc.invalidateQueries({ queryKey: orderKeys.all })
    },
  })
}
