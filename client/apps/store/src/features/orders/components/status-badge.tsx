import { Badge } from "@workspace/ui/components/badge"
import { ORDER_STATUS } from "../status"
import type { OrderStatus } from "../types"

/** Icon + text + colour, never colour alone. */
export function StatusBadge({ status }: { status: OrderStatus }) {
  const { label, icon: Icon, variant } = ORDER_STATUS[status] ?? ORDER_STATUS.pending
  return (
    <Badge variant={variant}>
      <Icon aria-hidden="true" />
      {label}
    </Badge>
  )
}
