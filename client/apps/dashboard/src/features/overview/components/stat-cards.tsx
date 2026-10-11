import { FolderTree, Package, ShoppingCart, Users } from "lucide-react"
import { useTranslation } from "react-i18next"
import { Card, CardAction, CardDescription, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { formatNumber } from "@/shared/lib/format"
import type { DashboardStats } from "../types"

export function StatCards({ stats }: { stats: DashboardStats }) {
  const { t } = useTranslation()
  const items = [
    { label: t("overview.users"), value: stats.usersCount, icon: Users },
    { label: t("overview.orders"), value: stats.ordersCount, icon: ShoppingCart },
    { label: t("overview.products"), value: stats.productsCount, icon: Package },
    { label: t("overview.categories"), value: stats.categoriesCount, icon: FolderTree },
  ]
  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      {items.map(({ label, value, icon: Icon }) => (
        <Card key={label}>
          <CardHeader>
            <CardDescription>{label}</CardDescription>
            <CardTitle className="text-2xl">{formatNumber(value)}</CardTitle>
            <CardAction>
              <Icon className="size-4 text-muted-foreground" />
            </CardAction>
          </CardHeader>
        </Card>
      ))}
    </div>
  )
}
