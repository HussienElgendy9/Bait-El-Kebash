import { Navigate, Outlet, useLocation } from "react-router"
import { hasToken } from "@/features/auth"

export function RequireAuth() {
  const { pathname, search } = useLocation()
  if (!hasToken()) return <Navigate to={`/login?next=${encodeURIComponent(pathname + search)}`} replace />
  return <Outlet />
}
