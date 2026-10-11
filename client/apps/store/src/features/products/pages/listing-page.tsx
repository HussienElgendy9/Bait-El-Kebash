import { SearchX, SlidersHorizontal } from "lucide-react"
import { Link, useSearchParams } from "react-router"
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
import { Card } from "@workspace/ui/components/card"
import {
  Sheet,
  SheetContent,
  SheetFooter,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
  SheetClose,
} from "@workspace/ui/components/sheet"
import { Spinner } from "@workspace/ui/components/spinner"
import { CardGridSkeleton, LoadError, StateBlock } from "@/shared/components/states"
import { categoryBySlug, type CategorySlug } from "../categories"
import { CategoryChips } from "../components/category-chips"
import { PRICE_OPTIONS, PriceFilter, SortSelect } from "../components/product-filters"
import { ProductGrid } from "../components/product-grid"
import { useProducts } from "../queries"
import type { PriceRange, ProductSort } from "../types"

export function ListingPage() {
  const [params, setParams] = useSearchParams()
  const category = categoryBySlug(params.get("category"))
  const price = PRICE_OPTIONS.find((o) => o.value === params.get("price"))?.value
  const sort = (params.get("sort") as ProductSort | null) ?? "newest"
  const search = params.get("search") ?? undefined

  const query = useProducts({ category_id: category.id, price, sort, search })
  const items = query.data?.items ?? []
  const activeCount = price ? 1 : 0

  const set = (key: string, value?: string) =>
    setParams(
      (p) => {
        if (value && value !== "all") p.set(key, value)
        else p.delete(key)
        return p
      },
      { replace: true }
    )
  const clearAll = () => setParams(search ? { search } : {}, { replace: true })

  return (
    <div className="mx-auto flex max-w-300 flex-col gap-4 px-4 pt-4 pb-12 md:px-8">
      <div className="hidden flex-col gap-4 md:flex">
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link to="/">الرئيسية</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>الأضاحي</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
        <h1 className="text-4xl leading-tight font-bold text-primary">
          {search ? `نتايج البحث: ${search}` : "كل الأضاحي"}
        </h1>
      </div>

      <CategoryChips value={category.slug} onChange={(v: CategorySlug) => set("category", v)} />

      {/* desktop filters */}
      <Card className="hidden flex-row flex-wrap items-end gap-x-8 gap-y-4 px-5 md:flex">
        <PriceFilter value={price} onChange={(v?: PriceRange) => set("price", v)} />
        <div className="ms-auto">
          <SortSelect value={sort} onChange={(v) => set("sort", v)} showLabel />
        </div>
      </Card>

      {/* mobile toolbar */}
      <div className="flex items-center gap-2 md:hidden">
        <Sheet>
          <SheetTrigger asChild>
            <Button variant="outline" size="lg" className="h-11">
              <SlidersHorizontal />
              فلترة
              {activeCount > 0 && <Badge>{activeCount}</Badge>}
            </Button>
          </SheetTrigger>
          <SheetContent side="bottom">
            <SheetHeader>
              <SheetTitle className="text-xl font-bold">فلترة</SheetTitle>
            </SheetHeader>
            <div className="px-4">
              <PriceFilter value={price} onChange={(v?: PriceRange) => set("price", v)} />
            </div>
            <SheetFooter className="flex-row border-t border-border">
              <Button variant="link" size="lg" onClick={clearAll}>
                مسح
              </Button>
              <SheetClose asChild>
                <Button size="lg" className="grow">
                  عرض {items.length} نتيجة
                </Button>
              </SheetClose>
            </SheetFooter>
          </SheetContent>
        </Sheet>
        <div className="min-w-0 grow">
          <SortSelect value={sort} onChange={(v) => set("sort", v)} />
        </div>
      </div>

      {query.isPending ? (
        <CardGridSkeleton />
      ) : query.isError && !query.data ? (
        <LoadError onRetry={() => query.refetch()} />
      ) : items.length === 0 ? (
        <Card className="border-dashed py-10">
          <StateBlock icon={SearchX} title="مفيش أضاحي بالفلاتر دي" description="جرّب وزن أو سعر تاني، أو اسألنا على واتساب.">
            <Button variant="outline-primary" size="lg" className="h-11" onClick={clearAll}>
              مسح الفلاتر
            </Button>
          </StateBlock>
        </Card>
      ) : (
        <>
          <p aria-live="polite" className="text-sm text-muted-foreground">
            {items.length} أضحية
          </p>
          <ProductGrid products={items} />
          <div className="flex flex-col items-center gap-2 pt-2">
            {query.hasNextPage ? (
              <Button
                variant="outline-primary"
                size="lg"
                className="px-7"
                disabled={query.isFetchingNextPage}
                aria-busy={query.isFetchingNextPage}
                onClick={() => query.fetchNextPage()}
              >
                {query.isFetchingNextPage ? (
                  <>
                    <Spinner />
                    جاري التحميل…
                  </>
                ) : (
                  "عرض المزيد"
                )}
              </Button>
            ) : (
              <span className="text-sm text-muted-foreground">ده كل المتاح دلوقتي.</span>
            )}
            {query.isFetchNextPageError && <LoadError onRetry={() => query.fetchNextPage()} />}
          </div>
        </>
      )}
    </div>
  )
}
