import { queryOptions, useQuery } from "@tanstack/react-query"
import { getDashboard } from "./api"

export const overviewKeys = { all: ["overview"] as const }

export const dashboardOptions = () => queryOptions({ queryKey: overviewKeys.all, queryFn: getDashboard })

export const useDashboard = () => useQuery(dashboardOptions())
