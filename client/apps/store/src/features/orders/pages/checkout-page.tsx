import { zodResolver } from "@hookform/resolvers/zod"
import { CircleAlert, MessageCircle, RotateCw, ShoppingCart } from "lucide-react"
import { useEffect } from "react"
import { Controller, useForm, useWatch, type FieldPath } from "react-hook-form"
import { Link, useNavigate } from "react-router"
import { toast } from "sonner"
import { Alert, AlertDescription, AlertTitle } from "@workspace/ui/components/alert"
import { Button } from "@workspace/ui/components/button"
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@workspace/ui/components/card"
import {
  Field,
  FieldContent,
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldLegend,
  FieldSet,
  FieldTitle,
} from "@workspace/ui/components/field"
import { Input } from "@workspace/ui/components/input"
import { RadioGroup, RadioGroupItem } from "@workspace/ui/components/radio-group"
import { Separator } from "@workspace/ui/components/separator"
import { Spinner } from "@workspace/ui/components/spinner"
import { Textarea } from "@workspace/ui/components/textarea"
import { useMe, useUpdateMe } from "@/features/auth"
import { CartLine, useCart } from "@/features/cart"
import { ApiError } from "@/shared/api/client"
import { StateBlock, useOnline } from "@/shared/components/states"
import { price } from "@/shared/lib/format"
import { WHATSAPP_URL } from "@/shared/lib/whatsapp"
import { useCreateOrder } from "../queries"
import { checkoutSchema, CUTS, DAYS, type CheckoutInput } from "../schemas"

const FIELD_LABELS: Partial<Record<FieldPath<CheckoutInput>, string>> = {
  name: "الاسم",
  phone_number: "رقم الموبايل",
  address: "العنوان",
  delivery_day: "يوم التوصيل",
}

export function CheckoutPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const online = useOnline()
  const { data: me } = useMe()
  const createOrder = useCreateOrder()
  const updateMe = useUpdateMe()

  const form = useForm<CheckoutInput>({
    resolver: zodResolver(checkoutSchema),
    defaultValues: { cut: "quarters", name: "", phone_number: "", address: "", notes: "" },
  })
  const { errors, isSubmitted } = form.formState
  const [cut, day] = useWatch({ control: form.control, name: ["cut", "delivery_day"] })

  // Prefill delivery details from the profile once it loads.
  useEffect(() => {
    if (me) form.reset({ ...form.getValues(), name: me.name, phone_number: me.phone_number, address: me.address })
  }, [me, form])

  if (!cart.items.length)
    return (
      <div className="px-4 py-16">
        <StateBlock icon={ShoppingCart} title="السلة فاضية" description="اختار أضحيتك وحدد التقطيع في دقيقتين.">
          <Button size="lg" asChild>
            <Link to="/products">تصفّح الأضاحي</Link>
          </Button>
        </StateBlock>
      </div>
    )

  /** 422 `errors[key][0]` → the matching field; `items.N.product_id` (sold out) → root, naming the cart line. */
  const showServerErrors = (e: Error) => {
    if (!(e instanceof ApiError) || e.status !== 422) return
    for (const [key, [msg]] of Object.entries(e.errors)) {
      const line = key.match(/^items\.(\d+)\.product_id$/)
      const name = line && cart.items[Number(line[1])]?.product.name
      if (key in FIELD_LABELS) form.setError(key as FieldPath<CheckoutInput>, { message: msg })
      else if (name) form.setError("root", { message: `«${name}» مبقاش متاح. شيلها من السلة وكمّل.` })
      else form.setError("root", { message: msg })
    }
  }

  // The order snapshots address + phone from the profile, so save any edits there first.
  const onSubmit = form.handleSubmit(async ({ name, phone_number, address }) => {
    if (me && (name !== me.name || phone_number !== me.phone_number || address !== me.address)) {
      try {
        await updateMe.mutateAsync({ name, phone_number, address })
      } catch (e) {
        return showServerErrors(e as Error)
      }
    }
    createOrder.mutate(
      { items: cart.items.map((i) => ({ product_id: i.product.id, quantity: i.quantity })) },
      {
        onSuccess: (order) => {
          cart.clear()
          toast.success("تم تسجيل طلبك", { description: `رقم الطلب #${order.id}. هيوصلك إيميل بالتفاصيل.` })
          navigate(`/orders/${order.id}`, { replace: true })
        },
        onError: showServerErrors,
      }
    )
  })

  const invalid = (Object.keys(FIELD_LABELS) as FieldPath<CheckoutInput>[]).filter((k) => errors[k])
  const failure = createOrder.error ?? updateMe.error
  const serverFailed = !!failure && !(failure instanceof ApiError && failure.status === 422)
  const busy = createOrder.isPending || updateMe.isPending

  const submitButton = (
    <Button type="submit" form="checkout" size="xl" className="w-full" disabled={busy || !online}>
      {busy ? (
        <>
          <Spinner />
          جاري التأكيد…
        </>
      ) : online ? (
        "تأكيد الطلب"
      ) : (
        "مستني الاتصال…"
      )}
    </Button>
  )

  return (
    <div className="mx-auto flex max-w-300 flex-wrap items-start gap-8 px-4 pt-4 pb-28 md:px-8 md:pb-12">
      <form id="checkout" onSubmit={onSubmit} noValidate className="flex min-w-0 flex-[1_1_560px] flex-col gap-6">
        <h1 className="hidden text-3xl font-bold md:block">إتمام الطلب</h1>

        <Card>
          <CardHeader>
            <CardTitle className="text-lg font-bold">السلة</CardTitle>
          </CardHeader>
          <CardContent className="flex flex-col gap-4">
            {cart.items.map((item) => (
              <CartLine key={item.product.id} item={item} />
            ))}
          </CardContent>
        </Card>

        {/* TODO(api): cut is not stored on orders yet */}
        <Controller
          control={form.control}
          name="cut"
          render={({ field }) => (
            <FieldSet>
              <FieldLegend className="text-xl font-bold">تحب نقطّعها إزاي؟</FieldLegend>
              <RadioGroup value={field.value} onValueChange={field.onChange} className="grid grid-cols-2 gap-3">
                {CUTS.map((c) => (
                  <FieldLabel key={c.value} htmlFor={`cut-${c.value}`}>
                    <Field orientation="horizontal" className="min-h-18">
                      <RadioGroupItem value={c.value} id={`cut-${c.value}`} />
                      <FieldContent>
                        <FieldTitle className="text-base font-bold">{c.label}</FieldTitle>
                        <FieldDescription>{c.hint}</FieldDescription>
                      </FieldContent>
                    </Field>
                  </FieldLabel>
                ))}
              </RadioGroup>
            </FieldSet>
          )}
        />

        <FieldSet>
          <FieldLegend className="text-xl font-bold">التوصيل فين وإمتى؟</FieldLegend>
          {isSubmitted && invalid.length > 0 && (
            <Alert variant="destructive">
              <CircleAlert />
              <AlertTitle>
                {invalid.length === 1 ? "فيه حقل محتاج تعديل" : `فيه ${invalid.length} حقول محتاجة تعديل`}
              </AlertTitle>
              <AlertDescription>راجع الحقول المعلّمة تحت: {invalid.map((k) => FIELD_LABELS[k]).join("، ")}</AlertDescription>
            </Alert>
          )}
          {errors.root && (
            <Alert variant="destructive">
              <CircleAlert />
              <AlertTitle>{errors.root.message}</AlertTitle>
            </Alert>
          )}
          <FieldGroup>
            <Field data-invalid={!!errors.name}>
              <FieldLabel htmlFor="o-name">الاسم</FieldLabel>
              <Input id="o-name" autoComplete="name" aria-invalid={!!errors.name} {...form.register("name")} />
              <FieldError errors={[errors.name]} />
            </Field>
            <Field data-invalid={!!errors.phone_number}>
              <FieldLabel htmlFor="o-phone">رقم الموبايل</FieldLabel>
              <Input
                id="o-phone"
                type="tel"
                inputMode="numeric"
                autoComplete="tel"
                dir="ltr"
                placeholder="01xxxxxxxxx"
                aria-invalid={!!errors.phone_number}
                {...form.register("phone_number")}
              />
              <FieldError errors={[errors.phone_number]} />
            </Field>
            <Field data-invalid={!!errors.address}>
              <FieldLabel htmlFor="o-addr">العنوان بالتفصيل</FieldLabel>
              <Input id="o-addr" autoComplete="street-address" aria-invalid={!!errors.address} {...form.register("address")} />
              <FieldError errors={[errors.address]} />
            </Field>

            {/* TODO(api): delivery_day is not stored on orders yet */}
            <Controller
              control={form.control}
              name="delivery_day"
              render={({ field, fieldState }) => (
                <FieldSet data-invalid={fieldState.invalid}>
                  <FieldLegend variant="label">يوم التوصيل</FieldLegend>
                  <RadioGroup value={field.value ?? ""} onValueChange={field.onChange} className="grid grid-cols-3 gap-2">
                    {DAYS.map((d) => (
                      <FieldLabel key={d.value} htmlFor={`day-${d.value}`}>
                        <Field orientation="horizontal" className="justify-center">
                          <RadioGroupItem value={d.value} id={`day-${d.value}`} aria-invalid={fieldState.invalid} className="sr-only" />
                          <FieldTitle className="text-[15px] font-semibold">{d.label}</FieldTitle>
                        </Field>
                      </FieldLabel>
                    ))}
                  </RadioGroup>
                  <FieldError errors={[fieldState.error]} />
                </FieldSet>
              )}
            />

            <Field>
              <FieldLabel htmlFor="o-notes">
                ملاحظات <span className="font-normal text-muted-foreground">(اختياري)</span>
              </FieldLabel>
              <Textarea id="o-notes" rows={2} {...form.register("notes")} />
            </Field>
          </FieldGroup>
        </FieldSet>

        <FieldSet>
          <FieldLegend variant="label">طريقة الدفع</FieldLegend>
          <RadioGroup value="cod">
            <FieldLabel htmlFor="pay-cod">
              <Field orientation="horizontal">
                <RadioGroupItem value="cod" id="pay-cod" />
                <FieldTitle className="font-semibold">الدفع عند الاستلام (كاش)</FieldTitle>
              </Field>
            </FieldLabel>
          </RadioGroup>
        </FieldSet>
      </form>

      <aside aria-label="ملخص الطلب" className="w-full md:sticky md:top-4 md:w-auto md:flex-[1_1_320px]">
        <Card>
          <CardHeader>
            <CardTitle className="text-lg font-bold">ملخص الطلب</CardTitle>
          </CardHeader>
          <CardContent className="flex flex-col gap-3 text-[15px]">
            {serverFailed && (
              <Alert variant="destructive">
                <CircleAlert />
                <AlertTitle>طلبك ما اتسجلش</AlertTitle>
                <AlertDescription>حصلت مشكلة عندنا. بياناتك محفوظة، جرّب تاني أو كلّمنا.</AlertDescription>
              </Alert>
            )}
            <div className="flex justify-between gap-4">
              <span className="text-muted-foreground">التقطيع</span>
              <span>{CUTS.find((c) => c.value === cut)?.label}</span>
            </div>
            <div className="flex justify-between gap-4">
              <span className="text-muted-foreground">يوم التوصيل</span>
              <span>{DAYS.find((d) => d.value === day)?.label ?? "—"}</span>
            </div>
            <div className="flex justify-between gap-4">
              <span className="text-muted-foreground">سعر الأضاحي</span>
              <span>{price(cart.total)}</span>
            </div>
            <div className="flex justify-between gap-4">
              <span className="text-muted-foreground">التوصيل</span>
              <span>[حسب المنطقة]</span>
            </div>
            <Separator />
            <div className="flex items-baseline justify-between gap-4">
              <span className="font-bold">الإجمالي</span>
              <strong className="text-2xl text-price">{price(cart.total)}</strong>
            </div>
          </CardContent>
          <CardFooter className="hidden flex-col gap-2 pb-4 md:flex">
            {submitButton}
            {serverFailed && (
              <Button variant="whatsapp" size="lg" className="w-full" asChild>
                <a href={WHATSAPP_URL} target="_blank" rel="noreferrer">
                  <MessageCircle />
                  كمّل الطلب على واتساب
                </a>
              </Button>
            )}
            <span className="text-sm text-muted-foreground">الدفع كاش عند الاستلام</span>
          </CardFooter>
        </Card>
      </aside>

      {/* mobile sticky action */}
      <div className="fixed inset-x-0 bottom-0 z-30 flex items-center gap-3 border-t border-border bg-card px-4 py-3 md:hidden">
        <div className="flex flex-col leading-snug">
          <span className="text-[13px] text-muted-foreground">الإجمالي</span>
          <strong className="text-[22px] text-price">{price(cart.total)}</strong>
        </div>
        <div className="grow">{serverFailed ? <RetryOrWhatsapp busy={busy} /> : submitButton}</div>
      </div>
    </div>
  )
}

function RetryOrWhatsapp({ busy }: { busy: boolean }) {
  return (
    <div className="flex gap-2">
      <Button type="submit" form="checkout" size="xl" className="grow" disabled={busy}>
        <RotateCw />
        حاول تاني
      </Button>
      <Button variant="whatsapp" size="xl" asChild>
        <a href={WHATSAPP_URL} target="_blank" rel="noreferrer" aria-label="كمّل الطلب على واتساب">
          <MessageCircle />
        </a>
      </Button>
    </div>
  )
}
