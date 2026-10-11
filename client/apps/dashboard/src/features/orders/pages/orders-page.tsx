import { Eye } from "lucide-react"
import { useState } from "react"
import { useTranslation } from "react-i18next"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { Card, CardDescription, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { Separator } from "@workspace/ui/components/separator"
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from "@workspace/ui/components/sheet"
import { Skeleton } from "@workspace/ui/components/skeleton"
import { useDashboard } from "@/features/overview"
import { ConfirmButton } from "@/shared/components/confirm-button"
import { DataTable } from "@/shared/components/data-table"
import { formatDate, formatNumber, price } from "@/shared/lib/format"
import { NEXT_STATUS, type Order, type OrderStatus } from "@/shared/types/models"
import { getOrders } from "../api"
import { orderKeys, useUpdateOrderStatus } from "../queries"

const total = (o: Order) => o.order_items?.reduce((s, i) => s + Number(i.price_snapshot), 0) ?? 0

export function OrdersPage() {
  const { t } = useTranslation()
  const [open, setOpen] = useState<Order>()

  return (
    <div className="grid gap-4">
      <StatusCounts />
      <DataTable
        queryKey={orderKeys.all}
        fetch={getOrders}
        columns={[
          { header: "#", cell: (o) => o.id },
          { header: t("orders.customer"), cell: (o) => o.user?.name },
          { header: t("orders.items"), cell: (o) => formatNumber(o.order_items?.length ?? 0) },
          { header: t("orders.total"), cell: (o) => price(total(o)) },
          { header: t("orders.status"), cell: (o) => <StatusBadge status={o.status} /> },
          { header: t("orders.date"), cell: (o) => formatDate(o.created_at) },
          {
            header: t("table.actions"),
            className: "w-px",
            cell: (o) => (
              <Button variant="ghost" size="icon" aria-label={t("actions.view")} onClick={() => setOpen(o)}>
                <Eye />
              </Button>
            ),
          },
        ]}
      />
      <OrderSheet order={open} onClose={() => setOpen(undefined)} />
    </div>
  )
}

function StatusBadge({ status }: { status: OrderStatus }) {
  const { t } = useTranslation()
  return <Badge variant={`status-${status}`}>{t(`overview.status.${status}`)}</Badge>
}

function StatusCounts() {
  const { t } = useTranslation()
  const { data } = useDashboard()
  const items = [
    { label: t("orders.all"), value: data?.ordersCount },
    { label: t("overview.status.pending"), value: data?.pendingOrders },
    { label: t("overview.status.processing"), value: data?.processingOrders },
    { label: t("overview.status.completed"), value: data?.completedOrders },
    { label: t("overview.status.cancelled"), value: data?.cancelledOrders },
  ]
  return (
    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
      {items.map(({ label, value }) => (
        <Card key={label}>
          <CardHeader>
            <CardDescription>{label}</CardDescription>
            <CardTitle className="text-2xl">{value === undefined ? <Skeleton className="h-8 w-12" /> : formatNumber(value)}</CardTitle>
          </CardHeader>
        </Card>
      ))}
    </div>
  )
}

function OrderSheet({ order, onClose }: { order?: Order; onClose: () => void }) {
  const { t, i18n } = useTranslation()
  const update = useUpdateOrderStatus()
  const setStatus = (status: OrderStatus) => order && update.mutate({ id: order.id, status }, { onSuccess: onClose })

  return (
    <Sheet open={!!order} onOpenChange={(o) => !o && onClose()}>
      <SheetContent side={i18n.dir() === "rtl" ? "left" : "right"} dir={i18n.dir()} className="overflow-y-auto">
        {order && (
          <>
            <SheetHeader>
              <SheetTitle>{t("orders.title", { id: order.id })}</SheetTitle>
              <SheetDescription>{formatDate(order.created_at)}</SheetDescription>
            </SheetHeader>
            <div className="grid gap-4 px-4 pb-4 text-sm">
              <StatusBadge status={order.status} />
              <div className="grid gap-1">
                <span className="font-medium">{order.user?.name}</span>
                <span dir="ltr" className="text-start text-muted-foreground">{order.user?.phone_number}</span>
                <span className="text-muted-foreground">{order.user?.address}</span>
              </div>
              <Separator />
              <ul className="grid gap-2">
                {order.order_items?.map((i) => (
                  <li key={i.id} className="flex justify-between gap-2">
                    <span>
                      {i.product?.name} × {i.quantity}
                    </span>
                    <span>{price(i.price_snapshot)}</span>
                  </li>
                ))}
              </ul>
              <Separator />
              <div className="flex justify-between font-semibold">
                <span>{t("orders.total")}</span>
                <span>{price(total(order))}</span>
              </div>
              {NEXT_STATUS[order.status].length > 0 && (
                <div className="flex flex-wrap gap-2">
                  {NEXT_STATUS[order.status].map((s) =>
                    s === "cancelled" ? (
                      <ConfirmButton
                        key={s}
                        title={t("orders.cancelTitle", { id: order.id })}
                        confirmLabel={t("orders.cancel")}
                        onConfirm={() => setStatus(s)}
                      >
                        <Button variant="outline" disabled={update.isPending}>
                          {t("orders.cancel")}
                        </Button>
                      </ConfirmButton>
                    ) : (
                      <Button key={s} disabled={update.isPending} onClick={() => setStatus(s)}>
                        {t(`orders.to.${s}`)}
                      </Button>
                    )
                  )}
                </div>
              )}
            </div>
          </>
        )}
      </SheetContent>
    </Sheet>
  )
}
