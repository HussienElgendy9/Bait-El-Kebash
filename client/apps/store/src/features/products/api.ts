import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { Category, Product, ProductListParams } from "./types"

export const getProducts = (params: ProductListParams, page: number) =>
  api<Paginated<Product>>("/products", { params: { ...params, page } })

export const getProduct = (id: number) => api<Product>(`/products/${id}`)

// ponytail: first page only (12); paginate if the shop ever has more categories.
export const getCategories = () => api<Paginated<Category>>("/categories").then((r) => r.data)
