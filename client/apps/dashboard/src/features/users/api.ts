import { api } from "@/shared/api/client"
import type { Paginated } from "@/shared/types/api"
import type { User } from "@/shared/types/models"

export const getCustomers = (page: number) => api<Paginated<User>>("/admin/customers", { params: { page } })
export const getAdmins = (page: number) => api<Paginated<User>>("/admin/admins", { params: { page } })
/** super_admin only. */
export const updateRole = ({ id, role }: { id: number; role: "admin" | "customer" }) =>
  api(`/admin/users/${id}/role`, { method: "PATCH", body: { role } })
