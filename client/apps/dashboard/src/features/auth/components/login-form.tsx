import { zodResolver } from "@hookform/resolvers/zod"
import { CircleAlert } from "lucide-react"
import { useForm } from "react-hook-form"
import { useTranslation } from "react-i18next"
import { Alert, AlertTitle } from "@workspace/ui/components/alert"
import { Button } from "@workspace/ui/components/button"
import { Field, FieldError, FieldGroup, FieldLabel } from "@workspace/ui/components/field"
import { Input } from "@workspace/ui/components/input"
import { Spinner } from "@workspace/ui/components/spinner"
import { ApiError } from "@/shared/api/client"
import { useLogin } from "../queries"
import { loginSchema, type LoginInput } from "../schemas"

export function LoginForm({ onDone }: { onDone: () => void }) {
  const { t } = useTranslation()
  const login = useLogin()
  const form = useForm<LoginInput>({ resolver: zodResolver(loginSchema), defaultValues: { email: "", password: "" } })
  const { errors } = form.formState

  const onError = (e: unknown) => {
    if (!(e instanceof ApiError)) return form.setError("root", { message: t("errors.generic") })
    if (e.status === 0) return form.setError("root", { message: t("errors.network") })
    if (e.status === 403) return form.setError("root", { message: t("auth.notAdmin") })
    // 422: Laravel `errors[field][0]` (server-language message).
    const email = e.errors.email?.[0]
    const password = e.errors.password?.[0]
    if (email) form.setError("email", { message: email })
    if (password) form.setError("password", { message: password })
    if (!email && !password) form.setError("root", { message: t("errors.generic") })
  }

  const submit = form.handleSubmit((v) => login.mutate(v, { onSuccess: onDone, onError }))
  // Client messages are i18n keys; server messages pass through t() unchanged.
  const msg = (m?: string) => (m ? t(m) : undefined)

  return (
    <form onSubmit={submit} noValidate>
      <FieldGroup>
        {errors.root && (
          <Alert variant="destructive">
            <CircleAlert />
            <AlertTitle>{errors.root.message}</AlertTitle>
          </Alert>
        )}
        <Field data-invalid={!!errors.email}>
          <FieldLabel htmlFor="email">{t("auth.email")}</FieldLabel>
          <Input id="email" type="email" autoComplete="email" dir="ltr" aria-invalid={!!errors.email} {...form.register("email")} />
          <FieldError>{msg(errors.email?.message)}</FieldError>
        </Field>
        <Field data-invalid={!!errors.password}>
          <FieldLabel htmlFor="password">{t("auth.password")}</FieldLabel>
          <Input
            id="password"
            type="password"
            autoComplete="current-password"
            dir="ltr"
            aria-invalid={!!errors.password}
            {...form.register("password")}
          />
          <FieldError>{msg(errors.password?.message)}</FieldError>
        </Field>
        <Button type="submit" disabled={login.isPending}>
          {login.isPending && <Spinner />}
          {t(login.isPending ? "auth.submitting" : "auth.submit")}
        </Button>
      </FieldGroup>
    </form>
  )
}
