import { ChevronsUpDown, FolderTree, LayoutDashboard, LogOut, Package, ShieldCheck, ShoppingCart, Users } from "lucide-react"
import { useTranslation } from "react-i18next"
import { NavLink, Outlet, useLocation, useNavigate } from "react-router"
import { Avatar, AvatarFallback } from "@workspace/ui/components/avatar"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@workspace/ui/components/dropdown-menu"
import { Separator } from "@workspace/ui/components/separator"
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarProvider,
  SidebarTrigger,
} from "@workspace/ui/components/sidebar"
import { useLogout, useMe } from "@/features/auth"
import { LanguageToggle } from "@/shared/components/language-toggle"

const NAV = [
  { to: "/", key: "nav.overview", icon: LayoutDashboard },
  { to: "/orders", key: "nav.orders", icon: ShoppingCart },
  { to: "/products", key: "nav.products", icon: Package },
  { to: "/categories", key: "nav.categories", icon: FolderTree },
  { to: "/users", key: "nav.users", icon: Users },
  { to: "/roles", key: "nav.roles", icon: ShieldCheck },
] as const

export function DashboardLayout() {
  const { t, i18n } = useTranslation()
  const dir = i18n.dir()
  const { pathname } = useLocation()
  const title = NAV.find((n) => n.to === pathname)?.key ?? "nav.overview"

  return (
    <SidebarProvider>
      <Sidebar side={dir === "rtl" ? "right" : "left"} dir={dir}>
        <SidebarHeader>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton size="lg">
                <div className="grid">
                  <span className="font-semibold">{t("appName")}</span>
                  <span className="text-xs text-muted-foreground">{t("dashboard")}</span>
                </div>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
          <SidebarGroup>
            <SidebarGroupContent>
              <SidebarMenu>
                {NAV.map(({ to, key, icon: Icon }) => (
                  <SidebarMenuItem key={to}>
                    <SidebarMenuButton asChild isActive={pathname === to} tooltip={t(key)}>
                      <NavLink to={to} end>
                        <Icon />
                        <span>{t(key)}</span>
                      </NavLink>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                ))}
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        </SidebarContent>
        <SidebarFooter>
          <UserMenu />
        </SidebarFooter>
      </Sidebar>
      <SidebarInset>
        <header className="flex h-14 items-center gap-2 border-b px-4">
          <SidebarTrigger />
          <Separator orientation="vertical" className="h-4" />
          <h1 className="flex-1 font-medium">{t(title)}</h1>
          <LanguageToggle />
        </header>
        <div className="p-4">
          <Outlet />
        </div>
      </SidebarInset>
    </SidebarProvider>
  )
}

function UserMenu() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const { data: me } = useMe()
  const logout = useLogout()
  const initials = me?.name.slice(0, 2).toUpperCase()

  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <SidebarMenuButton size="lg">
              <Avatar>
                <AvatarFallback>{initials}</AvatarFallback>
              </Avatar>
              <div className="grid flex-1 text-start">
                <span className="truncate font-medium">{me?.name}</span>
                <span className="truncate text-xs text-muted-foreground">{me?.email}</span>
              </div>
              <ChevronsUpDown />
            </SidebarMenuButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="min-w-56">
            <DropdownMenuLabel>{me?.email}</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
              onClick={() => logout.mutate(undefined, { onSettled: () => navigate("/login", { replace: true }) })}
            >
              <LogOut />
              {t("actions.logout")}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarMenuItem>
    </SidebarMenu>
  )
}
