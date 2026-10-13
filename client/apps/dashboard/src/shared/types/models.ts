/** Mirrors server/app/Http/Resources/*.php. */

/** `role` is present only when the requester is an admin. */
export type User = {
  id: number
  name: string
  email: string
  phone_number: string
  address: string
  role?: "customer" | "admin" | "super_admin"
  orders_count?: number
}

export type Category = { id: number; name: string; products_count?: number }

export type Product = {
  id: number
  name: string
  description: string | null
  unit_price: number | string
  unit: string
  image: string | null
  is_available: boolean
  category?: Category
}

export const ORDER_STATUSES = ["pending", "processing", "completed", "cancelled"] as const
export type OrderStatus = (typeof ORDER_STATUSES)[number]

/** Mirrors the server's allowed transitions in Admin/OrderController@update. */
export const NEXT_STATUS: Record<OrderStatus, OrderStatus[]> = {
  pending: ["processing", "cancelled"],
  processing: ["completed", "cancelled"],
  completed: [],
  cancelled: [],
}

export type OrderItem = {
  id: number
  quantity: number
  unit_price: number | string
  /** Line total (unit_price × quantity at order time). */
  price_snapshot: number | string
  product?: Product
}

export type Order = {
  id: number
  status: OrderStatus
  /** Delivery details snapshotted from the customer's profile at order time. */
  address: string
  phone_number: string
  /** Sum of price_snapshot; Laravel may send a number or a decimal string. */
  total: number | string
  created_at: string
  user?: User
  order_items?: OrderItem[]
}
