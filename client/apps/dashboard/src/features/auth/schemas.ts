import { z } from "zod"

// Messages are i18n keys, translated where they're rendered.
export const loginSchema = z.object({
  email: z.email("auth.errors.email"),
  password: z.string().min(1, "auth.errors.password"),
})

export type LoginInput = z.infer<typeof loginSchema>
