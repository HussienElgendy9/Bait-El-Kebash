import { infiniteQueryOptions, queryOptions, useInfiniteQuery, useQuery } from "@tanstack/react-query"
import { getCategories, getProduct, getProducts } from "./api"
import type { Product, ProductListParams } from "./types"

export const productKeys = {
  all: ["products"] as const,
  list: (p: ProductListParams) => [...productKeys.all, "list", p] as const,
  detail: (id: number) => [...productKeys.all, "detail", id] as const,
  categories: ["categories"] as const,
}

// TODO(api): search/sort aren't supported by public GET /products, so apply them to the loaded pages.
function filterLocally(items: Product[], { search, sort }: ProductListParams) {
  let out = items
  if (search) out = out.filter((p) => p.name.includes(search) || p.description?.includes(search))
  if (sort === "price_asc") out = [...out].sort((a, b) => Number(a.unit_price) - Number(b.unit_price))
  if (sort === "price_desc") out = [...out].sort((a, b) => Number(b.unit_price) - Number(a.unit_price))
  return out
}

export const productListOptions = (params: ProductListParams) =>
  infiniteQueryOptions({
    queryKey: productKeys.list(params),
    queryFn: ({ pageParam }) => getProducts(params, pageParam),
    initialPageParam: 1,
    getNextPageParam: (last) =>
      last.meta.current_page < last.meta.last_page ? last.meta.current_page + 1 : undefined,
    select: (data) => ({
      items: filterLocally(data.pages.flatMap((p) => p.data), params),
      total: data.pages[0]?.meta.total ?? 0,
    }),
  })

export const productOptions = (id: number) =>
  queryOptions({ queryKey: productKeys.detail(id), queryFn: () => getProduct(id) })

export const useProducts = (params: ProductListParams) => useInfiniteQuery(productListOptions(params))
export const useProduct = (id: number) => useQuery(productOptions(id))
export const useCategories = () =>
  useQuery({ queryKey: productKeys.categories, queryFn: getCategories, staleTime: Infinity })
