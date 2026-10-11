/** `GET /admin/dashboard` — raw object, not wrapped in `data`. */
export type DashboardStats = {
  usersCount: number
  ordersCount: number
  productsCount: number
  categoriesCount: number
  pendingOrders: number
  processingOrders: number
  completedOrders: number
  cancelledOrders: number
  /** English month abbreviations, oldest first. */
  salesLabels: string[]
  /** Completed-order sales per month. */
  salesData: number[]
  categoryLabels: string[]
  categoryData: number[]
}
