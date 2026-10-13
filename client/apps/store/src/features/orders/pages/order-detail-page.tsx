import { MessageCircle, X } from "lucide-react"
import { toast } from "sonner"
import { Link, useParams } from "react-router"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@workspace/ui/components/alert-dialog"
import { Button } from "@workspace/ui/components/button"
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { Separator } from "@workspace/ui/components/separator"
import { Skeleton } from "@workspace/ui/components/skeleton"
import { Spinner } from "@workspace/ui/components/spinner"
import { ProductImage } from "@/features/products"
import { ApiError } from "@/shared/api/client"
import { Forbidden, LoadError, NotFound } from "@/shared/components/states"
import { formatDate, price } from "@/shared/lib/format"
import { WHATSAPP_URL } from "@/shared/lib/whatsapp"
import { StatusBadge } from "../components/status-badge"
import { useCancelOrder, useOrder } from "../queries"

export function OrderDetailPage() {
  const id = Number(useParams().id)
  const { data: order, isPending, isError, error, refetch } = useOrder(id)
  const cancel = useCancelOrder()

  if (isPending)
    return (
      <div className="mx-auto flex max-w-200 flex-col gap-4 px-4 pt-5 md:px-8">
        <Skeleton className="h-10 w-1/2" />
        <Skeleton className="h-64 rounded-xl" />
      </div>
    )
  if (isError) {
    if (error instanceof ApiError && error.status === 404) return <NotFound />
    if (error instanceof ApiError && error.status === 403) return <Forbidden />
    return (
      <div className="mx-auto max-w-200 px-4 py-8 md:px-8">
        <LoadError onRetry={() => refetch()} />
      </div>
    )
  }

  return (
    <div className="mx-auto flex max-w-200 flex-col gap-4 px-4 pt-5 pb-12 md:px-8">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex flex-col">
          <h1 className="text-2xl font-bold text-primary md:text-4xl">
            طلب <span dir="ltr">#{order.id}</span>
          </h1>
          <span className="text-muted-foreground">{formatDate(order.created_at)}</span>
        </div>
        <StatusBadge status={order.status} />
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-lg font-bold">الأضاحي</CardTitle>
        </CardHeader>
        <CardContent className="flex flex-col gap-3">
          {order.order_items.map((item) => (
            <div key={item.id} className="flex items-center gap-3">
              <div className="size-16 shrink-0 overflow-hidden rounded-lg">
                {item.product && <ProductImage product={item.product} />}
              </div>
              <div className="flex min-w-0 grow flex-col">
                <strong className="truncate">{item.product?.name}</strong>
                <span className="text-sm text-muted-foreground">
                  {item.quantity} × {price(item.unit_price)}
                </span>
              </div>
              <span className="font-bold">{price(item.price_snapshot)}</span>
            </div>
          ))}
          {/* TODO(api): show cut + delivery_day once orders store them */}
          <Separator />
          <div className="flex items-baseline justify-between">
            <span className="font-bold">الإجمالي</span>
            <strong className="text-2xl text-price">{price(order.total)}</strong>
          </div>
          <span className="text-sm text-muted-foreground">الدفع كاش عند الاستلام</span>
          <Separator />
          <div className="flex flex-col gap-1">
            <span className="font-bold">التوصيل</span>
            <span className="text-muted-foreground">{order.address}</span>
            <span dir="ltr" className="text-start text-muted-foreground">
              {order.phone_number}
            </span>
          </div>
        </CardContent>
        <CardFooter className="flex-wrap gap-2 pb-4">
          <Button variant="outline" size="lg" asChild>
            <Link to="/orders">كل طلباتي</Link>
          </Button>
          {order.status === "pending" && (
            <AlertDialog>
              <AlertDialogTrigger asChild>
                <Button variant="destructive" size="lg" disabled={cancel.isPending}>
                  {cancel.isPending ? <Spinner /> : <X />}
                  إلغاء الطلب
                </Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>تلغي طلب #{order.id}؟</AlertDialogTitle>
                  <AlertDialogDescription>تقدر تلغي الطلب طول ما إحنا لسه ما بدأناش نجهّزه.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>رجوع</AlertDialogCancel>
                  <AlertDialogAction
                    variant="destructive"
                    onClick={() =>
                      cancel.mutate(order.id, {
                        onSuccess: () => toast.success("اتلغى الطلب"),
                        onError: (e) => {
                          // 422: it moved past pending meanwhile; refetch shows the real status.
                          if (e instanceof ApiError && e.status === 422) {
                            toast.error("الطلب بدأ يتجهّز ومبقاش ينفع يتلغي.")
                            refetch()
                          } else toast.error("حصلت مشكلة. جرّب تاني.")
                        },
                      })
                    }
                  >
                    إلغاء الطلب
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          )}
          <Button variant="whatsapp" size="lg" asChild>
            <a href={WHATSAPP_URL} target="_blank" rel="noreferrer">
              <MessageCircle />
              كلّمنا على واتساب
            </a>
          </Button>
        </CardFooter>
      </Card>
    </div>
  )
}
