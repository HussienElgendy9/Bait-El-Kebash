import { CircleAlert, Lock, RotateCw, SearchX, WifiOff, type LucideIcon } from "lucide-react"
import { useSyncExternalStore, type ReactNode } from "react"
import { isRouteErrorResponse, Link, useRouteError } from "react-router"
import { Alert, AlertAction, AlertDescription, AlertTitle } from "@workspace/ui/components/alert"
import { Button } from "@workspace/ui/components/button"
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from "@workspace/ui/components/empty"
import { Skeleton } from "@workspace/ui/components/skeleton"
import { WHATSAPP_URL } from "@/shared/lib/whatsapp"

/** 500 GET / network: section card with retry; the rest of the page keeps working. */
export function LoadError({ onRetry }: { onRetry: () => void }) {
  const offline = !useOnline()
  return (
    <Alert variant="destructive">
      {offline ? <WifiOff /> : <CircleAlert />}
      <AlertTitle>{offline ? "مفيش اتصال بالإنترنت." : "مقدرناش نحمّل البيانات."}</AlertTitle>
      <AlertDescription>{offline ? "اتأكد من الإنترنت وجرّب تاني." : "جرّب تاني بعد شوية."}</AlertDescription>
      <AlertAction>
        <Button variant="outline" onClick={onRetry}>
          <RotateCw />
          حاول تاني
        </Button>
      </AlertAction>
    </Alert>
  )
}

type StateProps = {
  icon: LucideIcon
  title: string
  description?: ReactNode
  tone?: "brand" | "error"
  children?: ReactNode
}

/** Full-width empty / error block: icon + title + text + actions. */
export function StateBlock({ icon: Icon, title, description, tone = "brand", children }: StateProps) {
  return (
    <Empty>
      <EmptyHeader>
        <EmptyMedia variant={tone}>
          <Icon />
        </EmptyMedia>
        <EmptyTitle className="text-xl font-bold">{title}</EmptyTitle>
        {description && <EmptyDescription className="text-[15px]">{description}</EmptyDescription>}
      </EmptyHeader>
      {children && <EmptyContent className="flex-row flex-wrap justify-center">{children}</EmptyContent>}
    </Empty>
  )
}

/** Skeleton grid for card listings. */
export function CardGridSkeleton({ count = 8 }: { count?: number }) {
  return (
    <div className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 lg:grid-cols-4" aria-busy="true">
      {Array.from({ length: count }, (_, i) => (
        <Skeleton key={i} className="aspect-[3/4] rounded-xl" />
      ))}
    </div>
  )
}

const HomeButton = () => (
  <Button size="lg" variant="outline" asChild>
    <Link to="/">الرجوع للرئيسية</Link>
  </Button>
)

export function NotFound() {
  return (
    <div className="px-4 py-16">
      <StateBlock icon={SearchX} tone="error" title="الصفحة دي مش موجودة" description="ممكن يكون اللينك قديم أو فيه غلطة.">
        <Button size="lg" asChild>
          <Link to="/products">تصفّح الأضاحي</Link>
        </Button>
        <HomeButton />
      </StateBlock>
    </div>
  )
}

export function Forbidden() {
  return (
    <div className="px-4 py-16">
      <StateBlock icon={Lock} tone="error" title="مش مسموح لك تفتح الصفحة دي" description="لو شايف إن ده غلط، كلّمنا على واتساب.">
        <HomeButton />
        <Button size="lg" variant="whatsapp" asChild>
          <a href={WHATSAPP_URL} target="_blank" rel="noreferrer">
            كلّمنا على واتساب
          </a>
        </Button>
      </StateBlock>
    </div>
  )
}

/** Route `errorElement`. */
export function RouteError() {
  const error = useRouteError()
  if (isRouteErrorResponse(error) && error.status === 404) return <NotFound />
  return (
    <div className="px-4 py-16">
      <StateBlock icon={CircleAlert} tone="error" title="حصلت مشكلة عندنا" description="جرّب تاني أو كلّمنا على واتساب.">
        <Button size="lg" onClick={() => location.reload()}>
          <RotateCw />
          حاول تاني
        </Button>
        <HomeButton />
      </StateBlock>
    </div>
  )
}

const subscribe = (cb: () => void) => {
  addEventListener("online", cb)
  addEventListener("offline", cb)
  return () => {
    removeEventListener("online", cb)
    removeEventListener("offline", cb)
  }
}

export const useOnline = () => useSyncExternalStore(subscribe, () => navigator.onLine)

/** Network: sticky top bar. */
export function OfflineBar() {
  const online = useOnline()
  if (online) return null
  return (
    <Alert role="status" className="sticky top-0 z-50 rounded-none border-x-0 border-t-0">
      <WifiOff />
      <AlertTitle>مفيش اتصال بالإنترنت. هنحاول تاني لوحدنا.</AlertTitle>
    </Alert>
  )
}
