import { ArrowRight, MessageCircle, Search, ShoppingCart, UserRound } from "lucide-react"
import type { FormEvent } from "react"
import { Link, useLocation, useNavigate, useSearchParams } from "react-router"
import { Badge } from "@workspace/ui/components/badge"
import { Button } from "@workspace/ui/components/button"
import { InputGroup, InputGroupAddon, InputGroupInput } from "@workspace/ui/components/input-group"
import {
  NavigationMenu,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
} from "@workspace/ui/components/navigation-menu"
import { WHATSAPP_URL } from "@/shared/lib/whatsapp"
import { LogoMark } from "./logo"

const NAV = [
  { to: "/", label: "الرئيسية" },
  { to: "/products", label: "الأقسام" },
  { to: "/orders", label: "طلباتي" },
]

type HeaderProps = {
  cartCount: number
  accountHref: string
  /** Mobile title; defaults to the brand name. */
  title?: string
  /** Mobile: show a back button instead of the logo. */
  back?: boolean
  /** Mobile: show the search field under the bar. */
  search?: boolean
  /** Mobile: hide the bar entirely (page draws its own chrome). */
  bare?: boolean
}

function SearchField() {
  const navigate = useNavigate()
  const [params] = useSearchParams()

  function submit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const q = new FormData(e.currentTarget).get("search")?.toString().trim()
    navigate(q ? `/products?search=${encodeURIComponent(q)}` : "/products")
  }

  return (
    <form role="search" onSubmit={submit} className="w-full">
      <InputGroup>
        <InputGroupAddon>
          <Search />
        </InputGroupAddon>
        <InputGroupInput
          type="search"
          name="search"
          aria-label="ابحث عن أضحية"
          placeholder="ابحث: خروف برقي، ماعز…"
          defaultValue={params.get("search") ?? ""}
          key={params.get("search")}
        />
      </InputGroup>
    </form>
  )
}

function CartButton({ count }: { count: number }) {
  return (
    <Button variant="header" size="icon-lg" className="relative" asChild>
      <Link to="/checkout" aria-label={`السلة، فيها ${count} أضحية`}>
        <ShoppingCart />
        {count > 0 && (
          <Badge variant="price" className="absolute -end-1.5 -top-1.5">
            {count}
          </Badge>
        )}
      </Link>
    </Button>
  )
}

export function Header({ cartCount, accountHref, title = "بيت الكباش", back, search, bare }: HeaderProps) {
  const navigate = useNavigate()
  const { pathname } = useLocation()
  const isActive = (to: string) => (to === "/" ? pathname === "/" : pathname.startsWith(to))

  return (
    <header className="bg-primary text-primary-foreground">
      {/* mobile */}
      <div className={bare ? "hidden" : "md:hidden"}>
        <div className="flex min-h-12 items-center gap-2 py-2 ps-3 pe-2">
          {back ? (
            <Button variant="header" size="icon-lg" className="border-transparent" aria-label="رجوع" onClick={() => navigate(-1)}>
              <ArrowRight />
            </Button>
          ) : (
            <Link to="/" aria-label="بيت الكباش">
              <LogoMark />
            </Link>
          )}
          <h1 className="grow text-lg font-bold">{title}</h1>
          <CartButton count={cartCount} />
        </div>
        {search && (
          <div className="px-4 pb-3">
            <SearchField />
          </div>
        )}
      </div>

      {/* desktop */}
      <div className="hidden md:block">
        <div className="border-b border-primary-foreground/15 text-sm">
          <div className="mx-auto flex max-w-300 flex-wrap justify-between gap-x-6 gap-y-2 px-8 py-2 text-secondary">
            <span dir="ltr">[رقم الهاتف]</span>
            <span>[عنوان المزرعة]</span>
            <span>[مواعيد العمل]</span>
            <span className="font-semibold text-primary-foreground">توصيل يوم العيد</span>
          </div>
        </div>
        <div className="mx-auto flex max-w-300 items-center gap-6 px-8 py-3">
          <Link to="/" className="flex min-h-11 items-center gap-2.5">
            <LogoMark />
            <span className="text-[22px] font-bold">بيت الكباش</span>
          </Link>
          <NavigationMenu aria-label="القائمة الرئيسية">
            <NavigationMenuList className="gap-1">
              {NAV.map((l) => (
                <NavigationMenuItem key={l.to}>
                  <NavigationMenuLink variant="header" active={isActive(l.to)} asChild>
                    <Link to={l.to} aria-current={isActive(l.to) ? "page" : undefined}>
                      {l.label}
                    </Link>
                  </NavigationMenuLink>
                </NavigationMenuItem>
              ))}
            </NavigationMenuList>
          </NavigationMenu>
          <div className="max-w-105 flex-[1_1_260px] text-foreground">
            <SearchField />
          </div>
          <div className="ms-auto flex items-center gap-2">
            <CartButton count={cartCount} />
            <Button variant="whatsapp" size="lg" className="h-11" asChild>
              <a href={WHATSAPP_URL} target="_blank" rel="noreferrer">
                <MessageCircle />
                اطلب على واتساب
              </a>
            </Button>
            <Button variant="header" size="icon-lg" asChild>
              <Link to={accountHref} aria-label="حسابي">
                <UserRound />
              </Link>
            </Button>
          </div>
        </div>
      </div>
    </header>
  )
}
