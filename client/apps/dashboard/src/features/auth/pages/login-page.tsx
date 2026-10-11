import { useTranslation } from "react-i18next"
import { Navigate, useNavigate, useSearchParams } from "react-router"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { LanguageToggle } from "@/shared/components/language-toggle"
import { LoginForm } from "../components/login-form"
import { isAdmin, useMe } from "../queries"

export function LoginPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const next = params.get("next") ?? "/"
  const { data: me } = useMe()

  if (isAdmin(me)) return <Navigate to={next} replace />

  return (
    <div className="flex min-h-svh flex-col items-center justify-center gap-4 p-6">
      <Card className="w-full max-w-sm">
        <CardHeader>
          <CardTitle>{t("auth.title")}</CardTitle>
          <CardDescription>{t("auth.description")}</CardDescription>
        </CardHeader>
        <CardContent>
          <LoginForm onDone={() => navigate(next, { replace: true })} />
        </CardContent>
      </Card>
      <LanguageToggle />
    </div>
  )
}
