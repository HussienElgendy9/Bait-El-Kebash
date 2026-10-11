import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { Category } from "@/shared/types/models"

export const getCategories = (page: number, per_page?: number) =>
  api<Paginated<Category>>("/admin/categories", { params: { page, per_page } })
export const saveCategory = ({ id, name }: { id?: number; name: string }) =>
  api(id ? `/admin/categories/${id}` : "/admin/categories", { method: id ? "PUT" : "POST", body: { name } })
export const deleteCategory = (id: number) => api(`/admin/categories/${id}`, { method: "DELETE" })
