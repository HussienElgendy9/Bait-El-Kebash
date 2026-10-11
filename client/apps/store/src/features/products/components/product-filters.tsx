import { useId } from "react"
import { ToggleGroup, ToggleGroupItem } from "@workspace/ui/components/toggle-group"
import { Label } from "@workspace/ui/components/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@workspace/ui/components/select"
import type { PriceRange, ProductSort } from "../types"

export const PRICE_OPTIONS: { value: PriceRange; label: string }[] = [
  { value: "lt10", label: "أقل من 10,000" },
  { value: "p10", label: "10,000–15,000" },
  { value: "p15", label: "15,000–20,000" },
  { value: "gt20", label: "أكتر من 20,000" },
]

const SORT_OPTIONS: { value: ProductSort; label: string }[] = [
  { value: "price_asc", label: "الأقل سعرًا" },
  { value: "price_desc", label: "الأعلى سعرًا" },
  { value: "newest", label: "الأحدث" },
]

// TODO(api): weight filter (أقل من 35 / 35–45 / 45–55 / أكتر من 55 كجم) hidden until products have weight_min/max.

type PriceProps = { value?: PriceRange; onChange: (v?: PriceRange) => void }

export function PriceFilter({ value, onChange }: PriceProps) {
  const id = useId()
  return (
    <div role="group" aria-labelledby={id} className="flex flex-col gap-2">
      <span id={id} className="text-sm font-bold">
        السعر
      </span>
      <ToggleGroup
        type="single"
        variant="chip"
        size="chip"
        spacing={1.5}
        value={value ?? ""}
        onValueChange={(v) => onChange((v || undefined) as PriceRange | undefined)}
        className="flex-wrap"
      >
        {PRICE_OPTIONS.map((o) => (
          <ToggleGroupItem key={o.value} value={o.value}>
            {o.label}
          </ToggleGroupItem>
        ))}
      </ToggleGroup>
    </div>
  )
}

type SortProps = { value: ProductSort; onChange: (v: ProductSort) => void; showLabel?: boolean }

export function SortSelect({ value, onChange, showLabel }: SortProps) {
  const id = useId()
  return (
    <div className="flex flex-col gap-2">
      <Label htmlFor={id} className={showLabel ? "font-bold" : "sr-only"}>
        ترتيب حسب
      </Label>
      <Select value={value} onValueChange={(v) => onChange(v as ProductSort)}>
        <SelectTrigger id={id} className="w-full min-w-45">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {SORT_OPTIONS.map((o) => (
            <SelectItem key={o.value} value={o.value}>
              {o.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  )
}
