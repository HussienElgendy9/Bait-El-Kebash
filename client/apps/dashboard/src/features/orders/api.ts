import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { Order, OrderStatus } from "@/shared/types/models"

export const getOrders = (page: number) => api<Paginated<Order>>("/admin/orders", { params: { page } })
export const updateOrderStatus = ({ id, status }: { id: number; status: OrderStatus }) =>
  api(`/admin/orders/${id}`, { method: "PATCH", body: { status } })
