import { Minus, Plus, Trash2 } from "lucide-react"
import { Button } from "@workspace/ui/components/button"
import { ProductImage, weightLabel } from "@/features/products"
import { price } from "@/shared/lib/format"
import { useCart, type CartItem } from "../store"

export function CartLine({ item }: { item: CartItem }) {
  const { setQuantity, remove } = useCart()
  const { product: p, quantity } = item
  const meta = [weightLabel(p), p.age].filter(Boolean).join(" · ")

  return (
    <div className="flex items-center gap-3">
      <div className="size-16 shrink-0 overflow-hidden rounded-lg">
        <ProductImage product={p} />
      </div>
      <div className="flex min-w-0 grow flex-col">
        <strong className="truncate">{p.name}</strong>
        {meta && <span className="text-sm text-muted-foreground">{meta}</span>}
        <span className="text-sm font-bold text-price">{price(Number(p.unit_price) * quantity)}</span>
      </div>
      <div className="flex items-center gap-1">
        <Button variant="outline" size="icon-lg" aria-label="زوّد واحد" onClick={() => setQuantity(p.id, quantity + 1)}>
          <Plus />
        </Button>
        <span className="min-w-6 text-center font-bold" aria-label={`الكمية ${quantity}`}>
          {quantity}
        </span>
        {quantity > 1 ? (
          <Button variant="outline" size="icon-lg" aria-label="قلّل واحد" onClick={() => setQuantity(p.id, quantity - 1)}>
            <Minus />
          </Button>
        ) : (
          <Button variant="destructive" size="icon-lg" aria-label={`شيل ${p.name} من السلة`} onClick={() => remove(p.id)}>
            <Trash2 />
          </Button>
        )}
      </div>
    </div>
  )
}
