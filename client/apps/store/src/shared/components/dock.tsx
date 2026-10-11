import { House, LayoutGrid, MessageCircle, ReceiptText, ShoppingCart, UserRound } from "lucide-react"
import { NavLink } from "react-router"
import { Button } from "@workspace/ui/components/button"
import { WHATSAPP_URL } from "@/shared/lib/whatsapp"

type DockProps = { cartCount: number; accountHref: string; /** hide the mobile nav (page has a sticky CTA) */ bare?: boolean }

export function Dock({ cartCount, accountHref, bare }: DockProps) {
  const items = [
    { to: "/", label: "الرئيسية", icon: House, end: true },
    { to: "/products", label: "الأقسام", icon: LayoutGrid },
    { to: "/checkout", label: `السلة (${cartCount})`, icon: ShoppingCart },
    { to: "/orders", label: "طلباتي", icon: ReceiptText },
    { to: accountHref, label: "حسابي", icon: UserRound },
  ]

  return (
    <>
      <Button variant="whatsapp" size="fab" className={`fixed start-4 z-40 md:bottom-6 ${bare ? "bottom-23" : "bottom-21"}`} asChild>
        <a href={WHATSAPP_URL} target="_blank" rel="noreferrer" aria-label="تواصل معنا على واتساب">
          <MessageCircle />
        </a>
      </Button>
      <nav
        aria-label="التنقل السفلي"
        className={`fixed inset-x-0 bottom-0 z-30 grid-cols-5 border-t border-border bg-card py-1.5 md:hidden ${bare ? "hidden" : "grid"}`}
      >
        {items.map(({ to, label, icon: Icon, end }) => (
          <Button key={label} variant="nav" size="nav" asChild>
            <NavLink to={to} end={end}>
              <Icon />
              {label}
            </NavLink>
          </Button>
        ))}
      </nav>
    </>
  )
}
