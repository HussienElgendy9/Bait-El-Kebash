import { ArrowRight, Check, Heart, MessageCircle, SearchX, ShieldCheck, Truck } from "lucide-react"
import { useState } from "react"
import { Link, useNavigate, useParams } from "react-router"
import { Badge } from "@workspace/ui/components/badge"
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@workspace/ui/components/breadcrumb"
import { Button } from "@workspace/ui/components/button"
import { Card, CardContent, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { Skeleton } from "@workspace/ui/components/skeleton"
import { Toggle } from "@workspace/ui/components/toggle"
import { useCart } from "@/features/cart"
import { ApiError } from "@/shared/api/client"
import { LoadError, StateBlock } from "@/shared/components/states"
import { price } from "@/shared/lib/format"
import { WHATSAPP_URL } from "@/shared/lib/whatsapp"
import { isAvailable, weightLabel } from "../components/product-card"
import { ProductGallery } from "../components/product-gallery"
import { ProductGrid } from "../components/product-grid"
import { useProduct, useProducts } from "../queries"
import type { Product } from "../types"

const INCLUDED = ["الرأس", "الكرشة", "الذبح والتقطيع"]

function Similar({ product }: { product: Product }) {
  const { data } = useProducts({ category_id: product.category?.id })
  const items = data?.items.filter((p) => p.id !== product.id).slice(0, 4) ?? []
  if (!items.length) return null
  return (
    <section aria-labelledby="sim-title" className="flex flex-col gap-4">
      <h2 id="sim-title" className="text-[clamp(1.375rem,1.1rem+0.8vw,1.75rem)] font-bold">
        {product.category ? `أضاحي تانية من ${product.category.name}` : "أضاحي تانية"}
      </h2>
      <ProductGrid products={items} />
    </section>
  )
}

function NotFoundState() {
  return (
    <div className="px-4 py-16">
      <StateBlock
        icon={SearchX}
        tone="error"
        title="الأضحية دي مش موجودة"
        description="ممكن تكون اتباعت أو اتشالت. عندنا أضاحي تانية."
      >
        <Button size="lg" asChild>
          <Link to="/products">شوف أضاحي تانية</Link>
        </Button>
        <Button size="lg" variant="outline" asChild>
          <Link to="/">الرجوع للرئيسية</Link>
        </Button>
      </StateBlock>
    </div>
  )
}

export function ProductPage() {
  const id = Number(useParams().id)
  const navigate = useNavigate()
  const { add } = useCart()
  const [fav, setFav] = useState(false)
  const { data: p, isPending, isError, error, refetch } = useProduct(id)

  if (isPending)
    return (
      <div className="mx-auto grid max-w-300 gap-8 px-4 py-4 md:grid-cols-2 md:px-8">
        <Skeleton className="aspect-[4/3] rounded-xl" />
        <div className="flex flex-col gap-4">
          <Skeleton className="h-10 w-2/3" />
          <Skeleton className="h-8 w-1/3" />
          <Skeleton className="h-24" />
        </div>
      </div>
    )
  if (isError) {
    if (error instanceof ApiError && error.status === 404) return <NotFoundState />
    return (
      <div className="mx-auto max-w-300 px-4 py-8 md:px-8">
        <LoadError onRetry={() => refetch()} />
      </div>
    )
  }

  const ok = isAvailable(p)
  const weight = weightLabel(p)
  const order = () => {
    add(p)
    navigate("/checkout")
  }
  const specs = [
    ["الوزن", weight],
    ["العمر", p.age],
    ["السلالة", p.breed],
  ].filter(([, v]) => v) as [string, string][]

  const favToggle = (
    <Toggle
      variant="fav"
      size="icon"
      pressed={fav}
      onPressedChange={setFav}
      aria-label="أضف للمفضلة"
      className="absolute end-3 top-3 size-11"
    >
      <Heart />
    </Toggle>
  )

  return (
    <div>
      <div className="md:hidden">
        <ProductGallery
          product={p}
          overlay={
            <>
              <Button
                variant="overlay"
                size="icon-lg"
                className="absolute start-3 top-3"
                aria-label="رجوع"
                onClick={() => navigate(-1)}
              >
                <ArrowRight />
              </Button>
              {favToggle}
            </>
          }
        />
      </div>

      <div className="mx-auto flex max-w-300 flex-col gap-8 px-4 pt-4 pb-12 md:px-8">
        <Breadcrumb className="hidden md:block">
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link to="/">الرئيسية</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link to={p.category ? `/products?category=${p.category.id}` : "/products"}>
                  {p.category?.name ?? "الأضاحي"}
                </Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>{p.name}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        <div className="flex flex-wrap items-start gap-8">
          <div className="hidden min-w-0 flex-[1_1_520px] md:block">
            <ProductGallery product={p} overlay={favToggle} />
          </div>

          <div className="flex min-w-0 flex-[1_1_400px] flex-col gap-4">
            <div className="flex flex-wrap gap-2">
              <Badge variant="tag">
                <ShieldCheck />
                فحص بيطري
              </Badge>
              {ok ? (
                <Badge variant="outline-primary">
                  <Check />
                  متاح للحجز
                </Badge>
              ) : (
                <Badge variant="overlay">نفدت الكمية</Badge>
              )}
            </div>
            <div className="flex flex-col gap-0.5">
              <h1 className="text-[clamp(1.75rem,1.2rem+1.6vw,2.25rem)] leading-tight font-bold">{p.name}</h1>
              <span className="text-muted-foreground">
                {[p.breed && `سلالة ${p.breed}`, p.category && `القسم: ${p.category.name}`].filter(Boolean).join(" · ")}
              </span>
            </div>
            <div className="flex items-baseline gap-2">
              <span className="text-3xl font-bold text-price">{price(p.unit_price)}</span>
              <span className="text-[15px] text-muted-foreground">/ {p.unit}</span>
            </div>

            {specs.length > 0 && (
              <dl className="grid grid-cols-3 gap-2">
                {specs.map(([k, v]) => (
                  <Card key={k} size="sm" className="gap-0.5 px-3">
                    <dt className="text-[13px] text-muted-foreground">{k}</dt>
                    <dd className="text-base font-bold">{v}</dd>
                  </Card>
                ))}
              </dl>
            )}

            <Card size="sm">
              <CardHeader>
                <CardTitle className="text-base font-bold">السعر يشمل</CardTitle>
              </CardHeader>
              <CardContent>
                <ul className="flex flex-wrap gap-x-5 gap-y-2 text-[15px]">
                  {INCLUDED.map((x) => (
                    <li key={x} className="flex items-center gap-1.5">
                      <Check className="size-4 text-primary" />
                      {x}
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>

            <div className="flex items-start gap-3 text-[15px]">
              <Badge variant="icon-soft">
                <Truck />
              </Badge>
              <div className="flex flex-col">
                <span className="text-muted-foreground">تختار أول أو تاني أو تالت يوم. الدفع عند الاستلام.</span>
              </div>
            </div>

            {p.description && <p>{p.description}</p>}

            <div className="hidden flex-col gap-3 md:flex">
              <Button size="xl" disabled={!ok} onClick={order}>
                اطلب الأضحية دي
              </Button>
              <Button variant="outline-primary" size="lg" asChild>
                <a href={WHATSAPP_URL} target="_blank" rel="noreferrer">
                  <MessageCircle />
                  عندك سؤال؟ اسألنا على واتساب
                </a>
              </Button>
            </div>
          </div>
        </div>

        <Similar product={p} />
      </div>

      {/* mobile sticky CTA */}
      <div className="fixed inset-x-0 bottom-0 z-30 flex items-center gap-3 border-t border-border bg-card px-4 py-3 md:hidden">
        <div className="flex flex-col leading-snug">
          <span className="text-[13px] text-muted-foreground">السعر / {p.unit}</span>
          <strong className="text-[22px] text-price">{price(p.unit_price)}</strong>
        </div>
        <Button size="xl" className="grow" disabled={!ok} onClick={order}>
          اطلب الأضحية دي
        </Button>
      </div>
    </div>
  )
}
