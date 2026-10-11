import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { Product } from "@/shared/types/models"

export const getProducts = (page: number) => api<Paginated<Product>>("/admin/products", { params: { page } })

/** Multipart (image upload). PHP can't parse multipart PATCH, so updates POST with Laravel's `_method` spoof. */
export const saveProduct = ({ id, data }: { id?: number; data: FormData }) => {
  if (id) data.set("_method", "PATCH")
  return api(id ? `/admin/products/${id}` : "/admin/products", { method: "POST", body: data })
}

export const deleteProduct = (id: number) => api(`/admin/products/${id}`, { method: "DELETE" })
