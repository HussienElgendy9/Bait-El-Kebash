import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { CreateOrderInput, Order } from "./types"

export const getOrders = (page: number) => api<Paginated<Order>>("/orders", { params: { page } })
export const getOrder = (id: number) => api<Order>(`/orders/${id}`)
export const createOrder = (body: CreateOrderInput) =>
  api<{ message: string; order: Order }>("/orders", { method: "POST", body }).then((r) => r.order)
