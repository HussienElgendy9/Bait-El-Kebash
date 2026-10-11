import { api } from "@/shared/api/client"
import type { LoginInput, RegisterInput } from "./schemas"
import type { AuthResponse, User } from "./types"

export const login = (body: LoginInput) => api<AuthResponse>("/login", { method: "POST", body })
export const register = (body: RegisterInput) => api<AuthResponse>("/register", { method: "POST", body })
export const logout = () => api<{ message: string }>("/logout", { method: "POST" })
export const getMe = () => api<User>("/user")
