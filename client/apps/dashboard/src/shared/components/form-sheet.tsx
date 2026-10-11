import type { FormEvent, ReactNode } from "react"
import { useTranslation } from "react-i18next"
import { Button } from "@workspace/ui/components/button"
import { FieldGroup } from "@workspace/ui/components/field"
import { Sheet, SheetContent, SheetFooter, SheetHeader, SheetTitle } from "@workspace/ui/components/sheet"
import { Spinner } from "@workspace/ui/components/spinner"
import { ApiError } from "@/shared/api/client"

/** Side sheet holding a native form; submits its FormData. Native `required`/`min` handle client checks, the server's 422 handles the rest. */
export function FormSheet({
  title,
  open,
  onClose,
  pending,
  onSubmit,
  children,
}: {
  title: string
  open: boolean
  onClose: () => void
  pending: boolean
  onSubmit: (data: FormData) => void
  children: ReactNode
}) {
  const { t, i18n } = useTranslation()
  const submit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    onSubmit(new FormData(e.currentTarget))
  }
  return (
    <Sheet open={open} onOpenChange={(o) => !o && onClose()}>
      <SheetContent side={i18n.dir() === "rtl" ? "left" : "right"} dir={i18n.dir()} className="overflow-y-auto">
        <form onSubmit={submit} className="grid gap-4">
          <SheetHeader>
            <SheetTitle>{title}</SheetTitle>
          </SheetHeader>
          <FieldGroup className="px-4">{children}</FieldGroup>
          <SheetFooter>
            <Button type="submit" disabled={pending}>
              {pending && <Spinner />}
              {t("actions.save")}
            </Button>
          </SheetFooter>
        </form>
      </SheetContent>
    </Sheet>
  )
}

/** First Laravel 422 message for a field. */
export const fieldError = (e: unknown, key: string) => (e instanceof ApiError ? e.errors[key]?.[0] : undefined)
