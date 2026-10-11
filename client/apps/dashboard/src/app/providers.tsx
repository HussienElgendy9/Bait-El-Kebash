import { MutationCache, QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { ReactQueryDevtools } from "@tanstack/react-query-devtools"
import type { ReactNode } from "react"
import { toast } from "sonner"
import { Toaster } from "@workspace/ui/components/sonner"
import { TooltipProvider } from "@workspace/ui/components/tooltip"
import i18n from "./i18n"
import { ApiError } from "@/shared/api/client"
import { ThemeProvider } from "@/shared/components/theme-provider"

const queryClient = new QueryClient({
  // Every failed mutation surfaces once here; forms additionally show 422 field errors inline.
  mutationCache: new MutationCache({
    onError: (e, _v, _c, m) =>
      !m.meta?.inline &&
      toast.error(
        e instanceof ApiError && e.status === 0 ? i18n.t("errors.network") : e instanceof ApiError && e.status < 500 ? e.message : i18n.t("errors.generic")
      ),
  }),
  defaultOptions: {
    queries: {
      staleTime: 60_000,
      retry: (n, e) => !(e instanceof ApiError && e.status > 0 && e.status < 500) && n < 2,
    },
  },
})

export function Providers({ children }: { children: ReactNode }) {
  return (
    <ThemeProvider>
      <QueryClientProvider client={queryClient}>
        <TooltipProvider>{children}</TooltipProvider>
        <Toaster position="top-center" richColors />
        {import.meta.env.DEV && <ReactQueryDevtools />}
      </QueryClientProvider>
    </ThemeProvider>
  )
}
