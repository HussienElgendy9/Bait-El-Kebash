export type Category = { id: number; name: string }

/** Mirrors server/app/Http/Resources/ProductResource.php */
export type Product = {
  id: number
  name: string
  description: string | null
  /** Laravel decimal → string, e.g. "17800.00" */
  unit_price: string
  unit: string
  image: string | null
  is_available: boolean
  category?: Category
  // TODO(api): design-only fields, not on ProductResource yet
  breed?: string
  weight_min?: number
  weight_max?: number
  age?: string
  images?: string[]
}

export type PriceRange = "lt10" | "p10" | "p15" | "gt20"
export type ProductSort = "newest" | "price_asc" | "price_desc"

export type ProductListParams = {
  category_id?: number
  price?: PriceRange
  // TODO(api): public GET /products ignores search + sort; applied client-side in queries.ts.
  search?: string
  sort?: ProductSort
}
