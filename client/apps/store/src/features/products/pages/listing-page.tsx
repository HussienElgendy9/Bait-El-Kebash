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
import { CategoryChips } from "../components/category-chips"
import { PriceFilter, SortSelect } from "../components/product-filters"
import { ProductGrid } from "../components/product-grid"
import { useCategories, useProducts } from "../queries"
import type { ProductSort } from "../types"

export function ListingPage() {
  const [params, setParams] = useSearchParams()
  const categoryId = Number(params.get("category")) || undefined
  const min_price = Number(params.get("min_price")) || undefined
  const max_price = Number(params.get("max_price")) || undefined
  const sort = (params.get("sort") as ProductSort | null) ?? "newest"
  const search = params.get("search") ?? undefined

  const query = useProducts({ category_id: categoryId, min_price, max_price, sort, search })
  const items = query.data?.items ?? []
  const activeCount = (min_price ? 1 : 0) + (max_price ? 1 : 0)
  const category = useCategories().data?.find((c) => c.id === categoryId)
  const priceFilter = <PriceFilter min={min_price} max={max_price} onChange={(k, v) => set(k, v?.toString())} />

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
              <BreadcrumbPage>{category?.name ?? "الأضاحي"}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
        <h1 className="text-4xl leading-tight font-bold text-primary">
          {search ? `نتايج البحث: ${search}` : (category?.name ?? "كل الأضاحي")}
        </h1>
      </div>

      <CategoryChips value={categoryId} onChange={(id) => set("category", id?.toString())} />

      {/* desktop filters */}
      <Card className="hidden flex-row flex-wrap items-end gap-x-8 gap-y-4 px-5 md:flex">
        {priceFilter}
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
              {priceFilter}
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
          <StateBlock icon={SearchX} title="مفيش أضاحي بالفلاتر دي" description="جرّب قسم أو سعر تاني، أو اسألنا على واتساب.">
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
