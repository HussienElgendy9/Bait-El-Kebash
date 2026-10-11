import { categoryKeys, useCatalogMutation } from "@/features/categories"
import { deleteProduct, saveProduct } from "./api"

export const productKeys = { all: categoryKeys.products }

export const useSaveProduct = () => useCatalogMutation(saveProduct)
export const useDeleteProduct = () => useCatalogMutation(deleteProduct)
