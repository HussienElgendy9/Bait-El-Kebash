import { Skeleton } from "@workspace/ui/components/skeleton"
import { LoadError } from "@/shared/components/states"
import { CategoryChart } from "../components/category-chart"
import { OrderStatusChart } from "../components/order-status-chart"
import { SalesChart } from "../components/sales-chart"
import { StatCards } from "../components/stat-cards"
import { useDashboard } from "../queries"

export function OverviewPage() {
  const { data, isPending, isError, refetch } = useDashboard()

  if (isError) return <LoadError onRetry={() => refetch()} />
  if (isPending)
    return (
      <div className="grid gap-4" aria-busy="true">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {Array.from({ length: 4 }, (_, i) => (
            <Skeleton key={i} className="h-28" />
          ))}
        </div>
        <Skeleton className="h-80" />
      </div>
    )

  return (
    <div className="grid gap-4">
      <StatCards stats={data} />
      <SalesChart stats={data} />
      <div className="grid gap-4 lg:grid-cols-2">
        <CategoryChart stats={data} />
        <OrderStatusChart stats={data} />
      </div>
    </div>
  )
}
