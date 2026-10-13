import { Outlet, useMatches } from "react-router"
import { useMe } from "@/features/auth"
import { useCart } from "@/features/cart"
import { Dock } from "@/shared/components/dock"
import { Header } from "@/shared/components/header"
import { OfflineBar } from "@/shared/components/states"

/** Per-route mobile header config, set via `handle` in app/router.tsx. */
export type RouteHandle = {
  title?: string
  back?: boolean
  search?: boolean
  /** mobile: page draws its own top chrome */
  hideHeader?: boolean
  /** mobile: page has a sticky CTA instead of the bottom nav */
  hideNav?: boolean
}

export function AppLayout() {
  const { count } = useCart()
  const { data: me } = useMe()
  const { hideHeader, hideNav, ...handle } = (useMatches().at(-1)?.handle ?? {}) as RouteHandle
  const accountHref = me ? "/orders" : "/login"

  return (
    <div className="flex min-h-svh flex-col">
      <OfflineBar />
      <Header cartCount={count} accountHref={accountHref} {...handle} bare={hideHeader} />
      <main className="flex-1 pb-24 md:pb-0">
        <Outlet />
      </main>
      <Dock cartCount={count} accountHref={accountHref} bare={hideNav} />
    </div>
  )
}
