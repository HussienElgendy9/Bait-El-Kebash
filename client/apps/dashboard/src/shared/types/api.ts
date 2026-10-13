/** Laravel paginated resource collection. */
export type Paginated<T> = {
  data: T[]
  links: { first: string | null; last: string | null; prev: string | null; next: string | null }
  meta: { current_page: number; last_page: number; per_page: number; total: number }
}

/** Laravel error body (422 carries `errors`). */
export type ApiErrorBody = {
  message?: string
  errors?: Record<string, string[]>
}

/** List filters as `?key=value` query params (see DataTable/useFilter). */
export type Filters = Record<string, string>
