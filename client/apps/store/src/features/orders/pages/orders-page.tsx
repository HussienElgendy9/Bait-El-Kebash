import { ChevronLeft, LogOut, ReceiptText } from "lucide-react"
import { Link, useNavigate } from "react-router"
import { Button } from "@workspace/ui/components/button"
import { Card } from "@workspace/ui/components/card"
import { Skeleton } from "@workspace/ui/components/skeleton"
import { Spinner } from "@workspace/ui/components/spinner"
import { ProductImage } from "@/features/products"
import { useLogout, useMe } from "@/features/auth"
import { LoadError, StateBlock } from "@/shared/components/states"
import { formatDate, price } from "@/shared/lib/format"
import { StatusBadge } from "../components/status-badge"
import { useOrders } from "../queries"
import { orderTotal } from "../status"
import type { Order } from "../types"

function OrderRow({ order }: { order: Order }) {
  const first = order.order_items[0]?.product
  const more = order.order_items.length - 1
  return (
    <Card size="sm" className="p-0">
      <Link to={`/orders/${order.id}`} className="flex min-h-22 items-center gap-3 p-3">
        <div className="size-16 shrink-0 overflow-hidden rounded-lg">
          {first && <ProductImage product={first} />}
        </div>
        <div className="flex min-w-0 grow flex-col gap-0.5">
          <strong className="truncate text-base">
            {first?.name ?? "طلب"}
            {more > 0 && ` + ${more}`}
          </strong>
          <span className="text-sm text-muted-foreground">
            طلب <span dir="ltr">#{order.id}</span> · {formatDate(order.created_at)}
          </span>
          <span className="text-sm font-semibold text-price">{price(orderTotal(order))}</span>
        </div>
        <div className="flex flex-col items-end gap-1.5">
          <StatusBadge status={order.status} />
          <ChevronLeft className="size-5 text-muted-foreground" aria-hidden="true" />
        </div>
      </Link>
    </Card>
  )
}

export function OrdersPage() {
  const navigate = useNavigate()
  const { data: me } = useMe()
  const logout = useLogout()
  const orders = useOrders()

  return (
    <div className="mx-auto flex max-w-200 flex-col gap-4 px-4 pt-5 pb-12 md:px-8">
      <div className="flex items-center justify-between gap-4">
        <div className="flex flex-col">
          <h1 className="hidden text-4xl leading-tight font-bold text-primary md:block">طلباتي</h1>
          {me && <span className="text-muted-foreground">أهلاً {me.name}</span>}
        </div>
        <Button
          variant="outline"
          size="lg"
          className="h-11"
          disabled={logout.isPending}
          onClick={() => logout.mutate(undefined, { onSettled: () => navigate("/") })}
        >
          {logout.isPending ? <Spinner /> : <LogOut />}
          تسجيل الخروج
        </Button>
      </div>

      {orders.isPending ? (
        <div className="flex flex-col gap-2.5" aria-busy="true" aria-label="جاري تحميل طلباتك">
          {[1, 2, 3].map((i) => (
            <Skeleton key={i} className="h-22 rounded-xl" />
          ))}
        </div>
      ) : orders.isError && !orders.data ? (
        <LoadError onRetry={() => orders.refetch()} />
      ) : orders.data.length === 0 ? (
        <Card className="border-dashed py-12">
          <StateBlock icon={ReceiptText} title="لسه ما طلبتش أضحية" description="أول طلب هيظهر هنا وتقدر تتابع حالته.">
            <Button size="lg" asChild>
              <Link to="/products">تصفّح الأضاحي</Link>
            </Button>
          </StateBlock>
        </Card>
      ) : (
        <>
          <ul className="flex flex-col gap-2.5">
            {orders.data.map((o) => (
              <li key={o.id}>
                <OrderRow order={o} />
              </li>
            ))}
          </ul>
          {orders.hasNextPage && (
            <Button
              variant="outline-primary"
              size="lg"
              className="self-center px-7"
              disabled={orders.isFetchingNextPage}
              onClick={() => orders.fetchNextPage()}
            >
              {orders.isFetchingNextPage ? (
                <>
                  <Spinner />
                  جاري التحميل…
                </>
              ) : (
                "عرض المزيد"
              )}
            </Button>
          )}
        </>
      )}
    </div>
  )
}
