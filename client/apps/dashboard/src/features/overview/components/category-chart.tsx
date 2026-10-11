import { useTranslation } from "react-i18next"
import { Bar, BarChart, XAxis, YAxis } from "recharts"
import { Card, CardContent, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from "@workspace/ui/components/chart"
import type { DashboardStats } from "../types"

export function CategoryChart({ stats }: { stats: DashboardStats }) {
  const { t, i18n } = useTranslation()
  const config = { count: { label: t("overview.products"), color: "var(--chart-2)" } } satisfies ChartConfig
  const data = stats.categoryLabels.map((name, i) => ({ name, count: stats.categoryData[i] }))
  const rtl = i18n.dir() === "rtl"

  return (
    <Card>
      <CardHeader>
        <CardTitle>{t("overview.byCategory")}</CardTitle>
      </CardHeader>
      <CardContent>
        <ChartContainer config={config} className="aspect-auto h-64 w-full">
          <BarChart data={data} layout="vertical" accessibilityLayer>
            <XAxis type="number" dataKey="count" hide reversed={rtl} />
            <YAxis type="category" dataKey="name" tickLine={false} axisLine={false} orientation={rtl ? "right" : "left"} />
            <ChartTooltip content={<ChartTooltipContent hideLabel />} />
            <Bar dataKey="count" fill="var(--color-count)" radius={4} />
          </BarChart>
        </ChartContainer>
      </CardContent>
    </Card>
  )
}
