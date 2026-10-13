import { zodResolver } from "@hookform/resolvers/zod"
import { CircleAlert } from "lucide-react"
import type { InputHTMLAttributes } from "react"
import { useForm, type FieldValues, type Path, type UseFormReturn } from "react-hook-form"
import { Alert, AlertTitle } from "@workspace/ui/components/alert"
import { Button } from "@workspace/ui/components/button"
import { Field, FieldError, FieldGroup, FieldLabel } from "@workspace/ui/components/field"
import { Input } from "@workspace/ui/components/input"
import { Spinner } from "@workspace/ui/components/spinner"
import { ApiError } from "@/shared/api/client"
import { useLogin, useRegister } from "../queries"
import { loginSchema, registerSchema, type LoginInput, type RegisterInput } from "../schemas"

/** Laravel 422 `errors[field][0]` → the matching form field, anything else → root. */
function applyServerErrors<T extends FieldValues>(form: UseFormReturn<T>, e: unknown) {
  if (!(e instanceof ApiError)) return form.setError("root", { message: "حصلت مشكلة عندنا. جرّب تاني." })
  if (e.status === 0) return form.setError("root", { message: "مفيش اتصال بالإنترنت." })
  if (e.status === 429) return form.setError("root", { message: "محاولات كتير. استنى دقيقة وجرّب تاني." })
  if (e.status !== 422) return form.setError("root", { message: "حصلت مشكلة عندنا. جرّب تاني." })
  const fields = Object.keys(form.getValues())
  for (const [key, [msg]] of Object.entries(e.errors)) {
    form.setError((fields.includes(key) ? key : "root") as Path<T>, { message: msg })
  }
}

type TextFieldProps<T extends FieldValues> = {
  form: UseFormReturn<T>
  name: Path<T>
  label: string
} & Omit<InputHTMLAttributes<HTMLInputElement>, "form" | "name">

function TextField<T extends FieldValues>({ form, name, label, ...input }: TextFieldProps<T>) {
  const error = form.formState.errors[name]
  const id = `auth-${name}`
  return (
    <Field data-invalid={!!error}>
      <FieldLabel htmlFor={id}>{label}</FieldLabel>
      <Input id={id} aria-invalid={!!error} {...input} {...form.register(name)} />
      <FieldError errors={[error as { message?: string } | undefined]} />
    </Field>
  )
}

function RootError({ message }: { message?: string }) {
  if (!message) return null
  return (
    <Alert variant="destructive">
      <CircleAlert />
      <AlertTitle>{message}</AlertTitle>
    </Alert>
  )
}

function Submit({ busy, label, busyLabel }: { busy: boolean; label: string; busyLabel: string }) {
  return (
    <Button type="submit" size="xl" className="w-full" disabled={busy}>
      {busy && <Spinner />}
      {busy ? busyLabel : label}
    </Button>
  )
}

export function LoginForm({ onDone }: { onDone: () => void }) {
  const login = useLogin()
  const form = useForm<LoginInput>({ resolver: zodResolver(loginSchema), defaultValues: { email: "", password: "" } })
  const submit = form.handleSubmit((v) => login.mutate(v, { onSuccess: onDone, onError: (e) => applyServerErrors(form, e) }))

  return (
    <form onSubmit={submit} noValidate>
      <FieldGroup>
        <RootError message={form.formState.errors.root?.message} />
        <TextField form={form} name="email" label="الإيميل" type="email" autoComplete="email" dir="ltr" />
        <TextField form={form} name="password" label="كلمة السر" type="password" autoComplete="current-password" dir="ltr" />
        <Submit busy={login.isPending} label="دخول" busyLabel="جاري الدخول…" />
      </FieldGroup>
    </form>
  )
}

export function RegisterForm({ onDone }: { onDone: () => void }) {
  const register = useRegister()
  const form = useForm<RegisterInput>({
    resolver: zodResolver(registerSchema),
    defaultValues: { name: "", email: "", phone_number: "", address: "", password: "", password_confirmation: "" },
  })
  const submit = form.handleSubmit((v) => register.mutate(v, { onSuccess: onDone, onError: (e) => applyServerErrors(form, e) }))

  return (
    <form onSubmit={submit} noValidate>
      <FieldGroup>
        <RootError message={form.formState.errors.root?.message} />
        <TextField form={form} name="name" label="الاسم" autoComplete="name" />
        <TextField
          form={form}
          name="phone_number"
          label="رقم الموبايل"
          type="tel"
          inputMode="numeric"
          autoComplete="tel-national"
          placeholder="01001234567"
          dir="ltr"
        />
        <TextField form={form} name="email" label="الإيميل" type="email" autoComplete="email" dir="ltr" />
        <TextField form={form} name="address" label="العنوان بالتفصيل" autoComplete="street-address" />
        <TextField form={form} name="password" label="كلمة السر" type="password" autoComplete="new-password" dir="ltr" />
        <TextField
          form={form}
          name="password_confirmation"
          label="أكّد كلمة السر"
          type="password"
          autoComplete="new-password"
          dir="ltr"
        />
        <Submit busy={register.isPending} label="إنشاء حساب" busyLabel="جاري إنشاء الحساب…" />
      </FieldGroup>
    </form>
  )
}
