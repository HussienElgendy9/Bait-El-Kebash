import { CircleAlert, RotateCw, SearchX } from "lucide-react"
import { useTranslation } from "react-i18next"
import { Link, useRouteError, isRouteErrorResponse } from "react-router"
import { Alert, AlertAction, AlertDescription, AlertTitle } from "@workspace/ui/components/alert"
import { Button } from "@workspace/ui/components/button"
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from "@workspace/ui/components/empty"

/** Failed GET: inline alert with retry. */
export function LoadError({ onRetry }: { onRetry: () => void }) {
  const { t } = useTranslation()
  return (
    <Alert variant="destructive">
      <CircleAlert />
      <AlertTitle>{t("errors.load")}</AlertTitle>
      <AlertDescription>{t("errors.tryLater")}</AlertDescription>
      <AlertAction>
        <Button variant="outline" onClick={onRetry}>
          <RotateCw />
          {t("actions.retry")}
        </Button>
      </AlertAction>
    </Alert>
  )
}

/** Route `ErrorBoundary` and `*` catch-all. */
export function RouteError() {
  const { t } = useTranslation()
  const error = useRouteError()
  const notFound = error === undefined || (isRouteErrorResponse(error) && error.status === 404)
  return (
    <Empty>
      <EmptyHeader>
        <EmptyMedia variant="icon">{notFound ? <SearchX /> : <CircleAlert />}</EmptyMedia>
        <EmptyTitle>{t(notFound ? "errors.notFound" : "errors.generic")}</EmptyTitle>
        <EmptyDescription>{t("errors.tryLater")}</EmptyDescription>
      </EmptyHeader>
      <EmptyContent>
        <Button asChild>
          <Link to="/">{t("nav.overview")}</Link>
        </Button>
      </EmptyContent>
    </Empty>
  )
}
