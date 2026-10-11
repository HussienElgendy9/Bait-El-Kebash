import { useTranslation } from "react-i18next"
import { Pie, PieChart } from "recharts"
import { Card, CardContent, CardHeader, CardTitle } from "@workspace/ui/components/card"
import {
  ChartContainer,
  ChartLegend,
  ChartLegendContent,
  ChartTooltip,
  ChartTooltipContent,
  type ChartConfig,
} from "@workspace/ui/components/chart"
import type { DashboardStats } from "../types"

const STATUSES = ["pending", "processing", "completed", "cancelled"] as const

export function OrderStatusChart({ stats }: { stats: DashboardStats }) {
  const { t } = useTranslation()
  const config = Object.fromEntries(
    STATUSES.map((s, i) => [s, { label: t(`overview.status.${s}`), color: `var(--chart-${i + 1})` }])
  ) satisfies ChartConfig
  const data = STATUSES.map((s) => ({ status: s, count: stats[`${s}Orders`], fill: `var(--color-${s})` }))

  return (
    <Card>
      <CardHeader>
        <CardTitle>{t("overview.orderStatus")}</CardTitle>
      </CardHeader>
      <CardContent>
        <ChartContainer config={config} className="mx-auto aspect-square h-64">
          <PieChart>
            <ChartTooltip content={<ChartTooltipContent nameKey="status" hideLabel />} />
            <Pie data={data} dataKey="count" nameKey="status" innerRadius={50} />
            <ChartLegend content={<ChartLegendContent nameKey="status" />} />
          </PieChart>
        </ChartContainer>
      </CardContent>
    </Card>
  )
}
