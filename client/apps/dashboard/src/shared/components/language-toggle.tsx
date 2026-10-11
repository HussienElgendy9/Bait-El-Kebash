import { Languages } from "lucide-react"
import { useTranslation } from "react-i18next"
import { Button } from "@workspace/ui/components/button"

export function LanguageToggle() {
  const { t, i18n } = useTranslation()
  return (
    <Button variant="ghost" onClick={() => i18n.changeLanguage(i18n.language === "ar" ? "en" : "ar")}>
      <Languages />
      {t("actions.language")}
    </Button>
  )
}
