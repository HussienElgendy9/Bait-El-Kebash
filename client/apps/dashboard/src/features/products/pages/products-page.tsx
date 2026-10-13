import { ImageOff, Pencil, Plus, Trash2 } from "lucide-react"
import { useState } from "react"
import { useTranslation } from "react-i18next"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { Field, FieldError, FieldLabel } from "@workspace/ui/components/field"
import { Input } from "@workspace/ui/components/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@workspace/ui/components/select"
import { Textarea } from "@workspace/ui/components/textarea"
import { useAllCategories } from "@/features/categories"
import { ConfirmButton } from "@/shared/components/confirm-button"
import { DataTable, SearchFilter, useFilter } from "@/shared/components/data-table"
import { fieldError, FormSheet } from "@/shared/components/form-sheet"
import { price } from "@/shared/lib/format"
import type { Product } from "@/shared/types/models"
import { getProducts } from "../api"
import { productKeys, useDeleteProduct, useSaveProduct } from "../queries"

export function ProductsPage() {
  const { t } = useTranslation()
  // undefined = closed, null = creating
  const [editing, setEditing] = useState<Product | null>()
  const save = useSaveProduct()
  const remove = useDeleteProduct()
  const categories = useAllCategories()
  const [categoryId, setCategoryId] = useFilter("category_id")
  const close = () => (setEditing(undefined), save.reset())

  const submit = (data: FormData) => {
    // Untouched optional inputs: let the server keep its default / current value.
    for (const [k, v] of [...data]) {
      if ((v === "" && k !== "description") || (v instanceof File && !v.size)) data.delete(k)
    }
    save.mutate({ id: editing?.id, data }, { onSuccess: close })
  }

  const field = (name: string, label: string, input: React.ReactNode) => (
    <Field data-invalid={!!fieldError(save.error, name)}>
      <FieldLabel htmlFor={name}>{label}</FieldLabel>
      {input}
      <FieldError>{fieldError(save.error, name)}</FieldError>
    </Field>
  )

  return (
    <>
      <DataTable
        queryKey={productKeys.all}
        fetch={getProducts}
        toolbar={
          <>
            <SearchFilter placeholder={t("products.search")} />
            <Select value={categoryId ?? "all"} onValueChange={(v) => setCategoryId(v === "all" ? undefined : v)}>
              <SelectTrigger aria-label={t("fields.category")} className="w-full sm:w-44">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">{t("products.allCategories")}</SelectItem>
                {categories.data?.map((c) => (
                  <SelectItem key={c.id} value={String(c.id)}>
                    {c.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <Button onClick={() => setEditing(null)}>
            <Plus />
              {t("products.new")}
            </Button>
          </>
        }
        columns={[
          {
            header: t("fields.image"),
            className: "w-px",
            cell: (p) =>
              p.image ? (
                <img src={p.image} alt="" className="size-10 rounded-md object-cover" />
              ) : (
                <ImageOff className="size-5 text-muted-foreground" />
              ),
          },
          { header: t("fields.name"), cell: (p) => p.name },
          { header: t("fields.category"), cell: (p) => p.category?.name },
          { header: t("fields.price"), cell: (p) => price(p.unit_price) },
          { header: t("fields.unit"), cell: (p) => p.unit },
          {
            header: t("fields.availability"),
            cell: (p) => (
              <Badge variant={p.is_available ? "secondary" : "outline"}>
                {t(p.is_available ? "products.available" : "products.unavailable")}
              </Badge>
            ),
          },
          {
            header: t("table.actions"),
            className: "w-px",
            cell: (p) => (
              <div className="flex gap-1">
                <Button variant="ghost" size="icon" aria-label={t("actions.edit")} onClick={() => setEditing(p)}>
                  <Pencil />
                </Button>
                <ConfirmButton
                  title={t("products.deleteTitle", { name: p.name })}
                  confirmLabel={t("actions.delete")}
                  onConfirm={() => remove.mutate(p.id)}
                >
                  <Button variant="ghost" size="icon" aria-label={t("actions.delete")}>
                    <Trash2 />
                  </Button>
                </ConfirmButton>
              </div>
            ),
          },
        ]}
      />
      <FormSheet
        key={editing?.id ?? "new"}
        title={t(editing ? "products.edit" : "products.new")}
        open={editing !== undefined}
        onClose={close}
        pending={save.isPending}
        onSubmit={submit}
      >
        {field("name", t("fields.name"), <Input id="name" name="name" required maxLength={255} defaultValue={editing?.name} />)}
        {field(
          "category_id",
          t("fields.category"),
          <Select name="category_id" required defaultValue={editing?.category ? String(editing.category.id) : undefined}>
            <SelectTrigger id="category_id" className="w-full">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {categories.data?.map((c) => (
                <SelectItem key={c.id} value={String(c.id)}>
                  {c.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}
        {field(
          "unit_price",
          t("fields.price"),
          <Input id="unit_price" name="unit_price" type="number" required min={100} step="any" dir="ltr" defaultValue={editing?.unit_price} />
        )}
        {field(
          "is_available",
          t("fields.availability"),
          // Laravel's `boolean` rule takes "1"/"0", not "true"/"false".
          <Select name="is_available" defaultValue={editing?.is_available === false ? "0" : "1"}>
            <SelectTrigger id="is_available" className="w-full">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="1">{t("products.available")}</SelectItem>
              <SelectItem value="0">{t("products.unavailable")}</SelectItem>
            </SelectContent>
          </Select>
        )}
        {field("unit", t("fields.unit"), <Input id="unit" name="unit" maxLength={50} placeholder="0.5kg" defaultValue={editing?.unit} />)}
        {field(
          "description",
          t("fields.description"),
          <Textarea id="description" name="description" defaultValue={editing?.description ?? ""} />
        )}
        {field(
          "image",
          t("fields.image"),
          <Input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required={!editing} />
        )}
      </FormSheet>
    </>
  )
}
