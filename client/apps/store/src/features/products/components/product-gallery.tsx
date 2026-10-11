import { useEffect, useState, type ReactNode } from "react"
import { AspectRatio } from "@workspace/ui/components/aspect-ratio"
import { Badge } from "@workspace/ui/components/badge"
import { Carousel, CarouselContent, CarouselItem, type CarouselApi } from "@workspace/ui/components/carousel"
import { ToggleGroup, ToggleGroupItem } from "@workspace/ui/components/toggle-group"
import { ProductImage } from "./product-card"
import type { Product } from "../types"

/** Falls back to the single `image` until the API returns `images[]` (TODO(api)). */
export function ProductGallery({ product, overlay }: { product: Product; overlay?: ReactNode }) {
  const images = product.images?.length ? product.images : [product.image]
  const [api, setApi] = useState<CarouselApi>()
  const [current, setCurrent] = useState(0)

  useEffect(() => {
    if (!api) return
    const onSelect = () => setCurrent(api.selectedScrollSnap())
    api.on("select", onSelect)
    return () => void api.off("select", onSelect)
  }, [api])

  return (
    <div className="flex flex-col gap-3">
      <div className="relative overflow-hidden bg-secondary md:rounded-xl">
        <Carousel setApi={setApi} opts={{ direction: "rtl" }}>
          <CarouselContent className="ms-0">
            {images.map((src, i) => (
              <CarouselItem key={i} className="ps-0">
                <AspectRatio ratio={4 / 3}>
                  <ProductImage product={{ ...product, image: src, images: undefined }} />
                </AspectRatio>
              </CarouselItem>
            ))}
          </CarouselContent>
        </Carousel>
        {overlay}
        {images.length > 1 && (
          <Badge variant="overlay" className="absolute end-3 bottom-3 h-7" dir="ltr">
            {current + 1}/{images.length}
          </Badge>
        )}
      </div>
      {images.length > 1 && (
        <ToggleGroup
          type="single"
          variant="thumb"
          spacing={2}
          value={String(current)}
          onValueChange={(v) => v && api?.scrollTo(Number(v))}
          aria-label="صور الأضحية"
          className="w-full overflow-x-auto px-4 md:grid md:grid-cols-4 md:gap-3 md:px-0"
        >
          {images.map((_, i) => (
            <ToggleGroupItem
              key={i}
              value={String(i)}
              aria-label={`صورة ${i + 1} من ${images.length}`}
              className="h-14 w-18 shrink-0 md:aspect-[4/3] md:h-auto md:w-auto"
            >
              {i + 1}
            </ToggleGroupItem>
          ))}
        </ToggleGroup>
      )}
    </div>
  )
}
