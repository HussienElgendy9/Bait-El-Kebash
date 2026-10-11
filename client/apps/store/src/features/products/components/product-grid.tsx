import { useCart } from "@/features/cart"
import { ProductCard } from "./product-card"
import type { Product } from "../types"

export function ProductGrid({ products }: { products: Product[] }) {
  const { addWithToast } = useCart()
  return (
    <ul className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 lg:grid-cols-4">
      {products.map((p) => (
        <li key={p.id}>
          <ProductCard product={p} onAdd={addWithToast} />
        </li>
      ))}
    </ul>
  )
}
