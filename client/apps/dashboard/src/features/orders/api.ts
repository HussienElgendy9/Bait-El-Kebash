import { api } from "@/shared/api/client"
import type { Filters, Paginated } from "@/shared/types/api"
import type { Order, OrderStatus } from "@/shared/types/models"

export const getOrders = (page: number, filters?: Filters) =>
  api<Paginated<Order>>("/admin/orders", { params: { ...filters, page } })
export const updateOrderStatus = ({ id, status }: { id: number; status: OrderStatus }) =>
  api(`/admin/orders/${id}`, { method: "PATCH", body: { status } })
