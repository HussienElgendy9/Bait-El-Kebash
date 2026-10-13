import { Navigate, Outlet, useLocation } from "react-router"
import { Spinner } from "@workspace/ui/components/spinner"
import { useMe } from "@/features/auth"

export function RequireAuth() {
  const { pathname, search } = useLocation()
  const { data: me, isPending } = useMe()
  if (isPending)
    return (
      <div className="flex justify-center py-16">
        <Spinner />
      </div>
    )
  if (!me) return <Navigate to={`/login?next=${encodeURIComponent(pathname + search)}`} replace />
  return <Outlet />
}
