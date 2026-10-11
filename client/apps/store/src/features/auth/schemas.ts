import { z } from "zod"

export const loginSchema = z.object({
  email: z.email("اكتب إيميل صحيح"),
  password: z.string().min(1, "اكتب كلمة السر"),
})

export const registerSchema = z
  .object({
    name: z.string().trim().min(1, "اكتب اسمك"),
    email: z.email("اكتب إيميل صحيح"),
    phone_number: z.string().regex(/^01[0125][0-9]{8}$/, "رقم الموبايل لازم يبدأ بـ 01 ويكون 11 رقم"),
    address: z.string().trim().min(1, "اكتب العنوان").max(1000),
    password: z.string().min(8, "كلمة السر 8 حروف على الأقل"),
    password_confirmation: z.string(),
  })
  .refine((v) => v.password === v.password_confirmation, {
    path: ["password_confirmation"],
    message: "كلمتين السر مش زي بعض",
  })

export type LoginInput = z.infer<typeof loginSchema>
export type RegisterInput = z.infer<typeof registerSchema>
