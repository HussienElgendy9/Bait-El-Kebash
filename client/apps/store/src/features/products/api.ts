import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { Category, PriceRange, Product, ProductListParams } from "./types"

export const PRICE_RANGES: Record<PriceRange, [number, number]> = {
  lt10: [0, 10_000],
  p10: [10_000, 15_000],
  p15: [15_000, 20_000],
  gt20: [20_000, Infinity],
}

export function getProducts({ price, ...params }: ProductListParams, page: number) {
  const [min_price, hi] = price ? PRICE_RANGES[price] : []
  // Server's max_price is inclusive; nudge it so a boundary price lands in one range only.
  const max_price = hi === undefined || hi === Infinity ? undefined : hi - 0.01
  return api<Paginated<Product>>("/products", { params: { ...params, min_price, max_price, page } })
}

export const getProduct = (id: number) => api<Product>(`/products/${id}`)

// ponytail: first page only (12); paginate if the shop ever has more categories.
export const getCategories = () => api<Paginated<Category>>("/categories").then((r) => r.data)
