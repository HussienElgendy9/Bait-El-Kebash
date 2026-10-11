import { createBrowserRouter } from "react-router"
import { RouteError } from "@/shared/components/states"
import { DashboardLayout } from "./layouts/dashboard-layout"
import { RequireAdmin } from "./layouts/require-admin"

export const router = createBrowserRouter([
  {
    ErrorBoundary: RouteError,
    children: [
      {
        path: "login",
        lazy: () => import("@/features/auth/pages/login-page").then((m) => ({ Component: m.LoginPage })),
      },
      {
        Component: RequireAdmin,
        children: [
          {
            Component: DashboardLayout,
            children: [
              {
                index: true,
                lazy: () =>
                  import("@/features/overview/pages/overview-page").then((m) => ({ Component: m.OverviewPage })),
              },
              {
                path: "products",
                lazy: () =>
                  import("@/features/products/pages/products-page").then((m) => ({ Component: m.ProductsPage })),
              },
              {
                path: "categories",
                lazy: () =>
                  import("@/features/categories/pages/categories-page").then((m) => ({ Component: m.CategoriesPage })),
              },
              {
                path: "orders",
                lazy: () => import("@/features/orders/pages/orders-page").then((m) => ({ Component: m.OrdersPage })),
              },
              {
                path: "users",
                lazy: () => import("@/features/users/pages/users-page").then((m) => ({ Component: m.UsersPage })),
              },
              {
                path: "roles",
                lazy: () => import("@/features/users/pages/roles-page").then((m) => ({ Component: m.RolesPage })),
              },
            ],
          },
        ],
      },
      { path: "*", Component: RouteError },
    ],
  },
])
