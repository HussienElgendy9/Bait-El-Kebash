import { z } from "zod"

export const CUTS = [
  { value: "whole", label: "كاملة", hint: "من غير تقطيع" },
  { value: "halves", label: "أنصاف", hint: "نصين" },
  { value: "quarters", label: "أرباع", hint: "4 أجزاء" },
  { value: "small", label: "قطع صغيرة", hint: "جاهزة للطبخ" },
] as const

export const DAYS = [
  { value: "d1", label: "أول يوم العيد" },
  { value: "d2", label: "تاني يوم" },
  { value: "d3", label: "تالت يوم" },
] as const

export const checkoutSchema = z.object({
  // TODO(api): POST /orders doesn't take cut, delivery_day or notes; they're collected but not sent.
  cut: z.enum(["whole", "halves", "quarters", "small"]),
  delivery_day: z.enum(["d1", "d2", "d3"], "اختار يوم التوصيل."),
  name: z.string().trim().min(1, "اكتب اسمك."),
  phone_number: z.string().regex(/^01[0125][0-9]{8}$/, "رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 01."),
  address: z.string().trim().min(8, "اكتب المنطقة والشارع ورقم العمارة."),
  notes: z.string().optional(),
})

export type CheckoutInput = z.infer<typeof checkoutSchema>
