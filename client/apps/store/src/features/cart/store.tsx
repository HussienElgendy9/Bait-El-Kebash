import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from "react"
import { toast } from "sonner"
import type { Product } from "@/features/products"

export type CartItem = { product: Product; quantity: number }

type Cart = {
  items: CartItem[]
  count: number
  total: number
  add: (product: Product, quantity?: number) => void
  /** add + confirmation toast */
  addWithToast: (product: Product, quantity?: number) => void
  setQuantity: (id: number, quantity: number) => void
  remove: (id: number) => void
  clear: () => void
}

const KEY = "cart"
/** POST /orders: items.*.quantity max:100 */
export const MAX_QUANTITY = 100
const CartContext = createContext<Cart | null>(null)

function load(): CartItem[] {
  try {
    const items = JSON.parse(localStorage.getItem(KEY) ?? "[]")
    // Stale or foreign data under the same key → start empty instead of crashing.
    return Array.isArray(items) ? items.filter((x) => x?.product?.id != null && x.quantity > 0) : []
  } catch {
    return []
  }
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState(load)

  useEffect(() => localStorage.setItem(KEY, JSON.stringify(items)), [items])

  const value = useMemo<Cart>(() => {
    const setQuantity = (id: number, quantity: number) =>
      setItems((xs) =>
        quantity < 1
          ? xs.filter((x) => x.product.id !== id)
          : xs.map((x) => (x.product.id === id ? { ...x, quantity: Math.min(quantity, MAX_QUANTITY) } : x))
      )
    const add = (product: Product, quantity = 1) =>
      setItems((xs) =>
        xs.some((x) => x.product.id === product.id)
          ? xs.map((x) => (x.product.id === product.id ? { ...x, quantity: Math.min(x.quantity + quantity, MAX_QUANTITY) } : x))
          : [...xs, { product, quantity }]
      )
    return {
      items,
      count: items.reduce((n, x) => n + x.quantity, 0),
      total: items.reduce((n, x) => n + Number(x.product.unit_price) * x.quantity, 0),
      add,
      addWithToast: (product, quantity) => {
        add(product, quantity)
        toast.success("اتضافت للسلة", { description: product.name })
      },
      setQuantity,
      remove: (id) => setQuantity(id, 0),
      clear: () => setItems([]),
    }
  }, [items])

  return <CartContext value={value}>{children}</CartContext>
}

export function useCart() {
  const cart = useContext(CartContext)
  if (!cart) throw new Error("useCart must be used inside <CartProvider>")
  return cart
}
