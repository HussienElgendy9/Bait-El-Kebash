import { Pencil, Plus, Trash2 } from "lucide-react"
import { useState } from "react"
import { useTranslation } from "react-i18next"
import { Button } from "@workspace/ui/components/button"
import { Field, FieldError, FieldLabel } from "@workspace/ui/components/field"
import { Input } from "@workspace/ui/components/input"
import { ConfirmButton } from "@/shared/components/confirm-button"
import { DataTable } from "@/shared/components/data-table"
import { fieldError, FormSheet } from "@/shared/components/form-sheet"
import { formatNumber } from "@/shared/lib/format"
import type { Category } from "@/shared/types/models"
import { getCategories } from "../api"
import { categoryKeys, useDeleteCategory, useSaveCategory } from "../queries"

export function CategoriesPage() {
  const { t } = useTranslation()
  // undefined = closed, null = creating
  const [editing, setEditing] = useState<Category | null>()
  const save = useSaveCategory()
  const remove = useDeleteCategory()
  const close = () => (setEditing(undefined), save.reset())

  return (
    <>
      <DataTable
        queryKey={categoryKeys.all}
        fetch={getCategories}
        toolbar={
          <Button onClick={() => setEditing(null)}>
            <Plus />
            {t("categories.new")}
          </Button>
        }
        columns={[
          { header: t("fields.name"), cell: (c) => c.name },
          { header: t("overview.products"), cell: (c) => formatNumber(c.products_count ?? 0) },
          {
            header: t("table.actions"),
            className: "w-px",
            cell: (c) => (
              <div className="flex gap-1">
                <Button variant="ghost" size="icon" aria-label={t("actions.edit")} onClick={() => setEditing(c)}>
                  <Pencil />
                </Button>
                <ConfirmButton
                  title={t("categories.deleteTitle", { name: c.name })}
                  confirmLabel={t("actions.delete")}
                  onConfirm={() => remove.mutate(c.id)}
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
        title={t(editing ? "categories.edit" : "categories.new")}
        open={editing !== undefined}
        onClose={close}
        pending={save.isPending}
        onSubmit={(d) => save.mutate({ id: editing?.id, name: String(d.get("name")) }, { onSuccess: close })}
      >
        <Field data-invalid={!!fieldError(save.error, "name")}>
          <FieldLabel htmlFor="name">{t("fields.name")}</FieldLabel>
          <Input id="name" name="name" required maxLength={255} defaultValue={editing?.name} />
          <FieldError>{fieldError(save.error, "name")}</FieldError>
        </Field>
      </FormSheet>
    </>
  )
}
