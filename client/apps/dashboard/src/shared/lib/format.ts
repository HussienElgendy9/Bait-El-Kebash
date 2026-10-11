import i18n from "@/app/i18n"

const locale = () => (i18n.language === "ar" ? "ar-EG-u-nu-latn" : "en-US")

export const formatNumber = (n: number | string) =>
  new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 }).format(Number(n))

/** `17800` → `17,800 ج.م` / `EGP 17,800`. */
export const price = (n: number | string) => i18n.t("currency", { value: formatNumber(n) })

/** API month labels are English abbreviations (`"Oct"`) → localized month name. */
export const monthName = (abbr: string) =>
  new Intl.DateTimeFormat(locale(), { month: "short" }).format(new Date(`${abbr} 1, 2000`))

export const formatDate = (iso: string) =>
  new Intl.DateTimeFormat(locale(), { dateStyle: "medium", timeStyle: "short" }).format(new Date(iso))
