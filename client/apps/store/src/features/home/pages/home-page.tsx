import { ArrowLeft, CalendarClock, SearchX } from "lucide-react"
import { useState } from "react"
import { Link } from "react-router"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { Card } from "@workspace/ui/components/card"
import { CategoryChips, ProductGrid, useProducts } from "@/features/products"
import { SheepIcon } from "@/shared/components/logo"
import { CardGridSkeleton, LoadError, StateBlock } from "@/shared/components/states"
import { price } from "@/shared/lib/format"
import { SiteFooter } from "../components/site-footer"

const STEPS = [
  { title: "اختار", text: "الأضحية بصورتها ووزنها وسعرها." },
  { title: "حدد التقطيع", text: "كاملة، أنصاف، أرباع أو قطع صغيرة." },
  { title: "استلم", text: "أول أو تاني أو تالت يوم العيد، وادفع عند الاستلام." },
]

export function HomePage() {
  const [cat, setCat] = useState<number>()
  const products = useProducts({ category_id: cat })
  const featured = products.data?.items.slice(0, 4) ?? []
  const prices = featured.map((p) => Number(p.unit_price))

  return (
    <>
      <div className="mx-auto flex max-w-300 flex-col gap-8 px-4 py-6 md:gap-10 md:px-8 md:py-8">
        <section aria-labelledby="eid-title" className="flex flex-wrap overflow-hidden rounded-2xl bg-primary text-primary-foreground">
          <div className="flex flex-[1_1_340px] flex-col items-start gap-3.5 p-[clamp(20px,4vw,48px)]">
            <Badge variant="accent">
              <CalendarClock />
              اطلب قبل [التاريخ]
            </Badge>
            <h1 id="eid-title" className="text-[clamp(1.75rem,1.2rem+1.6vw,2.25rem)] leading-tight font-bold">
              أضحية العيد من مزرعتنا
              <br />
              لحد باب بيتك
            </h1>
            <p className="text-secondary">كل أضحية بصورتها الحقيقية ووزنها وسعرها. والدفع عند الاستلام.</p>
            {prices.length > 0 && (
              <span className="text-secondary">
                تبدأ من <strong className="text-[22px] text-primary-foreground">{price(Math.min(...prices))}</strong>
              </span>
            )}
            <Button variant="inverse" size="lg" className="mt-1 px-6" asChild>
              <a href="#popular">
                اختار أضحيتك
                <ArrowLeft />
              </a>
            </Button>
          </div>
          <div className="flex min-h-55 flex-[1_1_340px] items-center justify-center bg-secondary/15 text-secondary">
            <SheepIcon className="size-24" />
          </div>
        </section>

        <section id="categories" aria-labelledby="cat-title" className="flex flex-col gap-3">
          <h2 id="cat-title" className="text-xl font-bold">
            الأقسام
          </h2>
          <CategoryChips value={cat} onChange={setCat} />
        </section>

        <section id="popular" aria-labelledby="pop-title" className="flex scroll-mt-4 flex-col gap-4">
          <div className="flex items-center justify-between gap-4">
            <h2 id="pop-title" className="text-[clamp(1.375rem,1.1rem+0.8vw,1.75rem)] font-bold">
              الأكثر طلبًا
            </h2>
            <Button variant="link" className="h-11 text-base font-semibold" asChild>
              <Link to={cat === undefined ? "/products" : `/products?category=${cat}`}>
                عرض الكل
                <ArrowLeft />
              </Link>
            </Button>
          </div>
          {products.isPending ? (
            <CardGridSkeleton count={4} />
          ) : products.isError ? (
            <LoadError onRetry={() => products.refetch()} />
          ) : featured.length === 0 ? (
            <StateBlock
              icon={SearchX}
              title="مفيش أضاحي في القسم ده دلوقتي"
              description="جرّب قسم تاني، أو اسألنا على واتساب ونبلّغك أول ما يتوفر."
            >
              <Button size="lg" variant="outline" onClick={() => setCat(undefined)}>
                عرض كل الأقسام
              </Button>
            </StateBlock>
          ) : (
            <ProductGrid products={featured} />
          )}
        </section>

        <section aria-labelledby="steps-title" className="flex flex-col gap-4">
          <h2 id="steps-title" className="text-[clamp(1.375rem,1.1rem+0.8vw,1.75rem)] font-bold">
            3 خطوات بس
          </h2>
          <ol className="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-3">
            {STEPS.map((s, i) => (
              <li key={s.title}>
                <Card className="h-full flex-row items-start gap-3.5 p-5">
                  <Badge variant="step">{i + 1}</Badge>
                  <div className="flex flex-col gap-1">
                    <strong className="text-lg">{s.title}</strong>
                    <span className="text-[15px] text-muted-foreground">{s.text}</span>
                  </div>
                </Card>
              </li>
            ))}
          </ol>
        </section>
      </div>
      <SiteFooter />
    </>
  )
}
