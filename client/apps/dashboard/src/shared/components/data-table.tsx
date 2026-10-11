import { keepPreviousData, useQuery, type QueryKey } from "@tanstack/react-query"
import { ChevronLeft, ChevronRight } from "lucide-react"
import type { ReactNode } from "react"
import { useTranslation } from "react-i18next"
import { useSearchParams } from "react-router"
import { Button } from "@workspace/ui/components/button"
import { Skeleton } from "@workspace/ui/components/skeleton"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@workspace/ui/components/table"
import { formatNumber } from "@/shared/lib/format"
import type { Paginated } from "@/shared/types/api"
import { LoadError } from "./states"

export type Column<T> = { header: string; cell: (row: T) => ReactNode; className?: string }

/** Paginated Laravel list → table. Page lives in `?page=` so it survives reloads. */
export function DataTable<T extends { id: number }>({
  queryKey,
  fetch,
  columns,
  toolbar,
}: {
  queryKey: QueryKey
  fetch: (page: number) => Promise<Paginated<T>>
  columns: Column<T>[]
  toolbar?: ReactNode
}) {
  const { t } = useTranslation()
  const [params, setParams] = useSearchParams()
  const page = Number(params.get("page")) || 1
  const { data, isPending, isError, refetch } = useQuery({
    queryKey: [...queryKey, page],
    queryFn: () => fetch(page),
    placeholderData: keepPreviousData,
  })
  const go = (p: number) => setParams((s) => (s.set("page", String(p)), s))

  return (
    <div className="grid gap-4">
      <div className="flex flex-wrap items-center gap-2">
        <p className="flex-1 text-sm text-muted-foreground">
          {data && t("table.total", { count: data.meta.total, value: formatNumber(data.meta.total) })}
        </p>
        {toolbar}
      </div>
      {isError ? (
        <LoadError onRetry={() => refetch()} />
      ) : isPending ? (
        <Skeleton className="h-80" />
      ) : (
        <div className="rounded-lg border">
          <Table>
            <TableHeader>
              <TableRow>
                {columns.map((c) => (
                  <TableHead key={c.header} className={c.className}>
                    {c.header}
                  </TableHead>
                ))}
              </TableRow>
            </TableHeader>
            <TableBody>
              {data.data.length === 0 && (
                <TableRow>
                  <TableCell colSpan={columns.length} className="py-10 text-center text-muted-foreground">
                    {t("table.empty")}
                  </TableCell>
                </TableRow>
              )}
              {data.data.map((row) => (
                <TableRow key={row.id}>
                  {columns.map((c) => (
                    <TableCell key={c.header} className={c.className}>
                      {c.cell(row)}
                    </TableCell>
                  ))}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      )}
      {data && data.meta.last_page > 1 && (
        <div className="flex items-center justify-end gap-2 text-sm">
          <Button variant="outline" size="icon" disabled={page <= 1} onClick={() => go(page - 1)} aria-label={t("table.prev")}>
            <ChevronRight className="ltr:rotate-180" />
          </Button>
          {t("table.page", { page, last: data.meta.last_page })}
          <Button
            variant="outline"
            size="icon"
            disabled={page >= data.meta.last_page}
            onClick={() => go(page + 1)}
            aria-label={t("table.next")}
          >
            <ChevronLeft className="ltr:rotate-180" />
          </Button>
        </div>
      )}
    </div>
  )
}
