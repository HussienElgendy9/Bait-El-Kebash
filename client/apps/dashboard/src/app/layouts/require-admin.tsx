import { Navigate, Outlet, useLocation } from "react-router"
import { Spinner } from "@workspace/ui/components/spinner"
import { isAdmin, useMe } from "@/features/auth"

export function RequireAdmin() {
  const { pathname, search } = useLocation()
  const { data: me, isPending } = useMe()
  const login = <Navigate to={`/login?next=${encodeURIComponent(pathname + search)}`} replace />

  if (isPending)
    return (
      <div className="flex min-h-svh items-center justify-center">
        <Spinner />
      </div>
    )
  if (!isAdmin(me)) return login
  return <Outlet />
}
