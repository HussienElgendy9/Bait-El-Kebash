import { createBrowserRouter } from "react-router"
import { NotFound, RouteError } from "@/shared/components/states"
import { AppLayout } from "./layouts/app-layout"
import type { RouteHandle } from "./layouts/app-layout"
import { RequireAuth } from "./layouts/require-auth"

const h = (handle: RouteHandle) => handle

export const router = createBrowserRouter([
  {
    Component: AppLayout,
    ErrorBoundary: RouteError,
    children: [
      { index: true, lazy: () => import("@/features/home/pages/home-page").then((m) => ({ Component: m.HomePage })) },
      {
        path: "products",
        handle: h({ title: "الأضاحي", search: true }),
        lazy: () => import("@/features/products/pages/listing-page").then((m) => ({ Component: m.ListingPage })),
      },
      {
        path: "products/:id",
        handle: h({ hideHeader: true, hideNav: true }),
        lazy: () => import("@/features/products/pages/product-page").then((m) => ({ Component: m.ProductPage })),
      },
      {
        path: "login",
        handle: h({ title: "سجّل دخولك", back: true, hideNav: true }),
        lazy: () => import("@/features/auth/pages/login-page").then((m) => ({ Component: m.LoginPage })),
      },
      {
        Component: RequireAuth,
        children: [
          {
            path: "checkout",
            handle: h({ title: "إتمام الطلب", back: true, hideNav: true }),
            lazy: () => import("@/features/orders/pages/checkout-page").then((m) => ({ Component: m.CheckoutPage })),
          },
          {
            path: "orders",
            handle: h({ title: "طلباتي" }),
            lazy: () => import("@/features/orders/pages/orders-page").then((m) => ({ Component: m.OrdersPage })),
          },
          {
            path: "orders/:id",
            handle: h({ title: "تفاصيل الطلب", back: true }),
            lazy: () =>
              import("@/features/orders/pages/order-detail-page").then((m) => ({ Component: m.OrderDetailPage })),
          },
        ],
      },
      { path: "*", Component: NotFound },
    ],
  },
])
