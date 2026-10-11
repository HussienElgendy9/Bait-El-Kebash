import { CircleCheck, CircleX, Clock, RefreshCw, type LucideIcon } from "lucide-react"
import type { OrderStatus } from "./types"

export const ORDER_STATUS: Record<
  OrderStatus,
  { label: string; icon: LucideIcon; variant: `status-${OrderStatus}` }
> = {
  pending: { label: "قيد الانتظار", icon: Clock, variant: "status-pending" },
  processing: { label: "جاري التجهيز", icon: RefreshCw, variant: "status-processing" },
  completed: { label: "تم التسليم", icon: CircleCheck, variant: "status-completed" },
  cancelled: { label: "ملغي", icon: CircleX, variant: "status-cancelled" },
}

export const orderTotal = (o: { order_items: { price_snapshot: string }[] }) =>
  o.order_items.reduce((n, i) => n + Number(i.price_snapshot), 0)
