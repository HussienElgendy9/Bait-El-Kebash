import { queryOptions, useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { deleteCategory, getCategories, saveCategory } from "./api"

// Product rows embed their category, so category changes refresh products too.
export const categoryKeys = { all: ["categories"] as const, products: ["products"] as const }

/** Every category (for selects). ponytail: capped at 100 by the server; paginate the select if that's ever hit. */
export const allCategoriesOptions = () =>
  queryOptions({ queryKey: [...categoryKeys.all, "all"], queryFn: () => getCategories(1, 100).then((r) => r.data) })

export const useAllCategories = () => useQuery(allCategoriesOptions())

/** Mutation that refreshes categories + products (shared by both features). */
export function useCatalogMutation<V>(mutationFn: (v: V) => Promise<unknown>) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn,
    onSuccess: () => Promise.all(Object.values(categoryKeys).map((queryKey) => qc.invalidateQueries({ queryKey }))),
  })
}

export const useSaveCategory = () => useCatalogMutation(saveCategory)
export const useDeleteCategory = () => useCatalogMutation(deleteCategory)
