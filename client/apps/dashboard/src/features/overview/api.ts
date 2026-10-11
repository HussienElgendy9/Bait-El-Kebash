import { api } from "@/shared/api/client"
import type { DashboardStats } from "./types"

export const getDashboard = () => api<DashboardStats>("/admin/dashboard")
