import { ToggleGroup, ToggleGroupItem } from "@workspace/ui/components/toggle-group"
import { CATEGORIES, type CategorySlug } from "../categories"

type Props = { value: CategorySlug; onChange: (slug: CategorySlug) => void }

export function CategoryChips({ value, onChange }: Props) {
  return (
    <ToggleGroup
      type="single"
      variant="chip"
      size="chip"
      spacing={2}
      value={value}
      onValueChange={(v) => onChange((v || "all") as CategorySlug)}
      aria-label="تصفية حسب القسم"
      className="max-w-full overflow-x-auto pb-1"
    >
      {CATEGORIES.map((c) => (
        <ToggleGroupItem key={c.slug} value={c.slug} className="shrink-0 px-5">
          {c.label}
        </ToggleGroupItem>
      ))}
    </ToggleGroup>
  )
}
