import { CircleAlert } from "lucide-react"
import { Link, Navigate, useNavigate, useSearchParams } from "react-router"
import { Alert, AlertDescription, AlertTitle } from "@workspace/ui/components/alert"
import { Button } from "@workspace/ui/components/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@workspace/ui/components/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@workspace/ui/components/tabs"
import { LoginForm, RegisterForm } from "../components/auth-forms"
import { useMe } from "../queries"

// TODO(api): phone OTP flow (InputOTP) per Auth.dc.html — the API only has email/password POST /login + /register.
export function LoginPage() {
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const raw = params.get("next") ?? "/"
  const next = raw.startsWith("/") && !raw.startsWith("//") ? raw : "/" // same-origin paths only
  const done = () => navigate(next, { replace: true })
  const { data: me } = useMe()

  if (me) return <Navigate to={next} replace />

  return (
    <div className="flex justify-center px-4 pt-[clamp(24px,6vw,72px)] pb-12">
      <Card className="w-full max-w-105 gap-4 px-6 py-6">
        <CardHeader className="px-0">
          <CardTitle className="text-[28px] leading-tight font-bold">سجّل دخولك</CardTitle>
          <CardDescription className="text-[15px]">علشان تكمّل طلبك وتتابع حالته.</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col gap-4 px-0">
          {params.has("expired") && (
            <Alert variant="destructive">
              <CircleAlert />
              <AlertTitle>انتهت الجلسة</AlertTitle>
              <AlertDescription>سجّل دخولك تاني. السلة محفوظة.</AlertDescription>
            </Alert>
          )}
          <Tabs defaultValue="login" className="gap-4">
            <TabsList className="h-12 w-full">
              <TabsTrigger value="login">دخول</TabsTrigger>
              <TabsTrigger value="register">حساب جديد</TabsTrigger>
            </TabsList>
            <TabsContent value="login">
              <LoginForm onDone={done} />
            </TabsContent>
            <TabsContent value="register">
              <RegisterForm onDone={done} />
            </TabsContent>
          </Tabs>
        </CardContent>
        <CardFooter className="justify-center px-0 pb-0 text-[13px] text-muted-foreground">
          بالدخول إنت موافق على
          <Button variant="link" className="h-11 px-1 text-[13px]" asChild>
            <Link to="#terms">الشروط</Link>
          </Button>
          .
        </CardFooter>
      </Card>
    </div>
  )
}
