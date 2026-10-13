import { useId } from "react"
import { Input } from "@workspace/ui/components/input"
import { Label } from "@workspace/ui/components/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@workspace/ui/components/select"
import type { ProductSort } from "../types"

const SORT_OPTIONS: { value: ProductSort; label: string }[] = [
  { value: "price_asc", label: "الأقل سعرًا" },
  { value: "price_desc", label: "الأعلى سعرًا" },
  { value: "newest", label: "الأحدث" },
]

// TODO(api): weight filter (أقل من 35 / 35–45 / 45–55 / أكتر من 55 كجم) hidden until products have weight_min/max.

type PriceValue = { min?: number; max?: number }
type PriceProps = PriceValue & { onChange: (key: "min_price" | "max_price", v?: number) => void }

/** Commits on blur/Enter so typing doesn't fire a request per keystroke. */
export function PriceFilter({ min, max, onChange }: PriceProps) {
  const id = useId()
  const field = (key: "min_price" | "max_price", value: number | undefined, label: string) => (
    <Input
      key={value ?? ""} // re-sync when cleared from outside
      type="number"
      inputMode="numeric"
      min={0}
      step={100}
      dir="ltr"
      defaultValue={value}
      placeholder={label}
      aria-label={`السعر ${label}`}
      className="h-11 w-32"
      onBlur={(e) => onChange(key, Number(e.target.value) || undefined)}
      onKeyDown={(e) => e.key === "Enter" && e.currentTarget.blur()}
    />
  )
  return (
    <div role="group" aria-labelledby={id} className="flex flex-col gap-2">
      <span id={id} className="text-sm font-bold">
        السعر (جنيه)
      </span>
      <div className="flex items-center gap-2">
        {field("min_price", min, "من")}
        <span className="text-muted-foreground">–</span>
        {field("max_price", max, "إلى")}
      </div>
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
