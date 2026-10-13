import { api, ApiError } from "@/shared/api/client"
import type { LoginInput } from "./schemas"
import type { AuthResponse, User } from "./types"

export const login = (body: LoginInput) => api<AuthResponse>("/login", { method: "POST", body })
export const logout = () => api<{ message: string }>("/logout", { method: "POST" })
/** `null` for guests. */
export const getMe = () =>
  api<User>("/user").catch((e) => {
    if (e instanceof ApiError && e.status === 401) return null
    throw e
  })
