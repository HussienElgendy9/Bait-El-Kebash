import { ToggleGroup, ToggleGroupItem } from "@workspace/ui/components/toggle-group"
import { useCategories } from "../queries"

/** `undefined` = all categories. */
type Props = { value?: number; onChange: (categoryId?: number) => void }

export function CategoryChips({ value, onChange }: Props) {
  const { data: categories = [] } = useCategories()
  return (
    <ToggleGroup
      type="single"
      variant="chip"
      size="chip"
      spacing={2}
      value={String(value ?? "all")}
      onValueChange={(v) => onChange(v && v !== "all" ? Number(v) : undefined)}
      aria-label="تصفية حسب القسم"
      className="max-w-full overflow-x-auto pb-1"
    >
      <ToggleGroupItem value="all" className="shrink-0 px-5">
        الكل
      </ToggleGroupItem>
      {categories.map((c) => (
        <ToggleGroupItem key={c.id} value={String(c.id)} className="shrink-0 px-5">
          {c.name}
        </ToggleGroupItem>
      ))}
    </ToggleGroup>
  )
}
