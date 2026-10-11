import { ShieldPlus } from "lucide-react"
import { useTranslation } from "react-i18next"
import { Button } from "@workspace/ui/components/button"
import { useMe } from "@/features/auth"
import { ConfirmButton } from "@/shared/components/confirm-button"
import { DataTable, type Column } from "@/shared/components/data-table"
import { formatNumber } from "@/shared/lib/format"
import type { User } from "@/shared/types/models"
import { getCustomers } from "../api"
import { userKeys, useUpdateRole } from "../queries"

export function UsersPage() {
  const { t } = useTranslation()
  const { data: me } = useMe()
  const update = useUpdateRole()

  const columns: Column<User>[] = [
    { header: t("fields.name"), cell: (u) => u.name },
    { header: t("fields.email"), cell: (u) => <span dir="ltr">{u.email}</span> },
    { header: t("fields.phone"), cell: (u) => <span dir="ltr">{u.phone_number}</span> },
    { header: t("fields.address"), cell: (u) => u.address },
    { header: t("overview.orders"), cell: (u) => formatNumber(u.orders_count ?? 0) },
  ]
  // Only super admins may change roles (server enforces it too).
  if (me?.role === "super_admin")
    columns.push({
      header: t("table.actions"),
      className: "w-px",
      cell: (u) => (
        <ConfirmButton
          title={t("users.promoteTitle", { name: u.name })}
          confirmLabel={t("users.promote")}
          onConfirm={() => update.mutate({ id: u.id, role: "admin" })}
        >
          <Button variant="outline" size="sm">
            <ShieldPlus />
            {t("users.promote")}
          </Button>
        </ConfirmButton>
      ),
    })

  return <DataTable queryKey={userKeys.customers} fetch={getCustomers} columns={columns} />
}
