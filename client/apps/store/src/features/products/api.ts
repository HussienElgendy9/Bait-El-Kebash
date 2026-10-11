import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { PriceRange, Product, ProductListParams } from "./types"

export const PRICE_RANGES: Record<PriceRange, [number, number]> = {
  lt10: [0, 10_000],
  p10: [10_000, 15_000],
  p15: [15_000, 20_000],
  gt20: [20_000, Infinity],
}

export function getProducts({ price, ...params }: ProductListParams, page: number) {
  const [min_price, max_price] = price ? PRICE_RANGES[price] : []
  return api<Paginated<Product>>("/products", {
    params: { ...params, min_price, max_price: max_price === Infinity ? undefined : max_price, page },
  })
}

export const getProduct = (id: number) => api<Product>(`/products/${id}`)
