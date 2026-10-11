const num = new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 })
const date = new Intl.DateTimeFormat("ar-EG-u-nu-latn", { day: "numeric", month: "long", year: "numeric" })

/** `17800` or `"17800.00"` → `17,800 ج.م` (Western digits). */
export const price = (n: number | string) => `${num.format(Number(n))} ج.م`

/** ISO date → `11 أكتوبر 2026`. */
export const formatDate = (iso: string) => date.format(new Date(iso))
