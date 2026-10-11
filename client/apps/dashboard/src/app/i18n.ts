import i18n from "i18next"
import { initReactI18next } from "react-i18next"
import ar from "./locales/ar.json"
import en from "./locales/en.json"

export const LANGS = ["ar", "en"] as const
export type Lang = (typeof LANGS)[number]
const KEY = "lang"

const stored = localStorage.getItem(KEY)
const lng: Lang = stored === "en" ? "en" : "ar"

const applyDir = (l: string) => {
  document.documentElement.lang = l
  document.documentElement.dir = i18n.dir(l)
}

i18n.on("languageChanged", (l) => {
  localStorage.setItem(KEY, l)
  applyDir(l)
})

i18n.use(initReactI18next).init({
  resources: { ar: { translation: ar }, en: { translation: en } },
  lng,
  fallbackLng: "ar",
  interpolation: { escapeValue: false },
})
applyDir(lng)

export default i18n
