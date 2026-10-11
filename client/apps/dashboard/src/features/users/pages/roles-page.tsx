import { ShieldMinus } from "lucide-react"
import { useTranslation } from "react-i18next"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { useMe } from "@/features/auth"
import { ConfirmButton } from "@/shared/components/confirm-button"
import { DataTable } from "@/shared/components/data-table"
import { getAdmins } from "../api"
import { userKeys, useUpdateRole } from "../queries"

export function RolesPage() {
  const { t } = useTranslation()
  const { data: me } = useMe()
  const update = useUpdateRole()
  const canEdit = me?.role === "super_admin"

  return (
    <div className="grid gap-4">
      <p className="text-sm text-muted-foreground">{t(canEdit ? "roles.hint" : "roles.readOnly")}</p>
      <DataTable
        queryKey={userKeys.admins}
        fetch={getAdmins}
        columns={[
          { header: t("fields.name"), cell: (u) => u.name },
          { header: t("fields.email"), cell: (u) => <span dir="ltr">{u.email}</span> },
          {
            header: t("roles.role"),
            cell: (u) => (
              <Badge variant={u.role === "super_admin" ? "default" : "secondary"}>{t(`roles.${u.role}`)}</Badge>
            ),
          },
          {
            header: t("table.actions"),
            className: "w-px",
            // Super admins and yourself can't be demoted from here.
            cell: (u) =>
              canEdit &&
              u.role === "admin" &&
              u.id !== me?.id && (
                <ConfirmButton
                  title={t("roles.demoteTitle", { name: u.name })}
                  confirmLabel={t("roles.demote")}
                  onConfirm={() => update.mutate({ id: u.id, role: "customer" })}
                >
                  <Button variant="outline" size="sm">
                    <ShieldMinus />
                    {t("roles.demote")}
                  </Button>
                </ConfirmButton>
              ),
          },
        ]}
      />
    </div>
  )
}
