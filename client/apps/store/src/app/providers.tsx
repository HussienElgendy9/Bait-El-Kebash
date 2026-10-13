import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { ReactQueryDevtools } from "@tanstack/react-query-devtools"
import type { ReactNode } from "react"
import { DirectionProvider } from "@workspace/ui/components/direction"
import { Toaster } from "@workspace/ui/components/sonner"
import { CartProvider } from "@/features/cart"
import { ApiError } from "@/shared/api/client"

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60_000,
      retry: (n, e) => !(e instanceof ApiError && e.status > 0 && e.status < 500) && n < 2,
    },
  },
})

export function Providers({ children }: { children: ReactNode }) {
  return (
    <DirectionProvider dir="rtl">
      <QueryClientProvider client={queryClient}>
        <CartProvider>
          {children}
          <Toaster position="top-center" dir="rtl" richColors />
        </CartProvider>
        {import.meta.env.DEV && <ReactQueryDevtools buttonPosition="bottom-left" />}
      </QueryClientProvider>
    </DirectionProvider>
  )
}
