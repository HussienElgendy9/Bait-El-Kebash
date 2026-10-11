import { useTranslation } from "react-i18next"
import { Bar, BarChart, CartesianGrid, XAxis } from "recharts"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from "@workspace/ui/components/chart"
import { monthName, price } from "@/shared/lib/format"
import type { DashboardStats } from "../types"

export function SalesChart({ stats }: { stats: DashboardStats }) {
  const { t, i18n } = useTranslation()
  const config = { sales: { label: t("overview.sales"), color: "var(--chart-1)" } } satisfies ChartConfig
  const data = stats.salesLabels.map((m, i) => ({ month: monthName(m), sales: stats.salesData[i] }))

  return (
    <Card>
      <CardHeader>
        <CardTitle>{t("overview.sales")}</CardTitle>
        <CardDescription>{t("overview.salesDescription")}</CardDescription>
      </CardHeader>
      <CardContent>
        <ChartContainer config={config} className="aspect-auto h-64 w-full">
          <BarChart data={data} accessibilityLayer>
            <CartesianGrid vertical={false} />
            <XAxis dataKey="month" tickLine={false} axisLine={false} reversed={i18n.dir() === "rtl"} />
            <ChartTooltip content={<ChartTooltipContent formatter={(v) => price(Number(v))} />} />
            <Bar dataKey="sales" fill="var(--color-sales)" radius={4} />
          </BarChart>
        </ChartContainer>
      </CardContent>
    </Card>
  )
}
