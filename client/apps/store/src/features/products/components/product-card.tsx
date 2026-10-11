import { CalendarDays, Heart, Plus, ShieldCheck, Weight } from "lucide-react"
import { useState } from "react"
import { Link } from "react-router"
import { AspectRatio } from "@workspace/ui/components/aspect-ratio"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { Card } from "@workspace/ui/components/card"
import { Toggle } from "@workspace/ui/components/toggle"
import { cn } from "@workspace/ui/lib/utils"
import { SheepIcon } from "@/shared/components/logo"
import { price } from "@/shared/lib/format"
import type { Product } from "../types"

export const weightLabel = (p: Product) =>
  p.weight_min != null && p.weight_max != null ? `${p.weight_min}–${p.weight_max} كجم` : null

export const isAvailable = (p: Product) => p.is_available !== false // TODO(api): no stock flag yet

export function ProductImage({ product, className }: { product: Product; className?: string }) {
  const src = product.image ?? product.images?.[0]
  return src ? (
    <img src={src} alt={product.name} className={cn("size-full object-cover", className)} />
  ) : (
    <div className={cn("flex size-full items-center justify-center bg-muted text-muted-foreground", className)}>
      <SheepIcon className="size-16" />
    </div>
  )
}

type Props = { product: Product; onAdd: (p: Product) => void }

export function ProductCard({ product, onAdd }: Props) {
  const [fav, setFav] = useState(false)
  const ok = isAvailable(product)
  const weight = weightLabel(product)
  const href = `/products/${product.id}`

  return (
    <Card className="h-full gap-0 py-0">
      <AspectRatio ratio={4 / 3} className="relative bg-muted">
        <Link to={href} aria-label={weight ? `${product.name}، وزن ${weight}` : product.name}>
          <ProductImage product={product} className={cn(!ok && "grayscale")} />
        </Link>
        <Badge variant="secondary" className="absolute start-2 top-2 h-6.5 font-bold">
          <ShieldCheck />
          فحص بيطري
        </Badge>
        <Toggle
          variant="fav"
          size="icon"
          pressed={fav}
          onPressedChange={setFav}
          aria-label={fav ? "شيل من المفضلة" : "أضف للمفضلة"}
          className="absolute end-2 top-2"
        >
          <Heart />
        </Toggle>
        {!ok && (
          <div className="pointer-events-none absolute inset-0 flex items-center justify-center bg-muted-foreground/60">
            <Badge variant="overlay">نفدت الكمية</Badge>
          </div>
        )}
      </AspectRatio>

      <div className="flex grow flex-col gap-2 p-3">
        <div className="flex flex-col">
          <h3 className="text-base leading-snug font-semibold">
            <Link to={href}>{product.name}</Link>
          </h3>
          {product.breed && <span className="text-sm text-muted-foreground">سلالة {product.breed}</span>}
        </div>
        {(weight || product.age) && (
          <div className="flex flex-wrap gap-1.5">
            {weight && (
              <Badge variant="muted">
                <Weight />
                {weight}
              </Badge>
            )}
            {product.age && (
              <Badge variant="muted">
                <CalendarDays />
                {product.age}
              </Badge>
            )}
          </div>
        )}
        <div className="mt-auto flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5">
          <span className="text-[22px] leading-tight font-bold text-price">{price(product.unit_price)}</span>
          <span className="text-[13px] text-muted-foreground">/ {product.unit}</span>
        </div>
        {ok ? (
          <Button size="lg" className="h-11" onClick={() => onAdd(product)}>
            <Plus />
            أضف للسلة
          </Button>
        ) : (
          <Button size="lg" variant="outline" className="h-11" disabled>
            غير متاح
          </Button>
        )}
      </div>
    </Card>
  )
}
