import type { Product } from "@/features/products"

/** server: orders.status enum + Admin\OrderController@update */
export type OrderStatus = "pending" | "processing" | "completed" | "cancelled"

export type OrderItem = {
  id: number
  quantity: number
  unit_price: string
  price_snapshot: string
  product?: Product
}

/** Mirrors server/app/Http/Resources/OrderResource.php */
export type Order = {
  id: number
  status: OrderStatus
  created_at: string
  /** Delivery details snapshotted from the profile when the order was placed. */
  address: string
  phone_number: string
  /** Sum of price_snapshot; Laravel may send a number or a decimal string. */
  total: number | string
  order_items: OrderItem[]
  // TODO(api): not stored on orders yet
  cut?: string
  delivery_day?: string
}

export type CreateOrderInput = { items: { product_id: number; quantity: number }[] }
