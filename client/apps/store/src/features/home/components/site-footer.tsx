import { MessageCircle } from "lucide-react"
import { Button } from "@workspace/ui/components/button"
import { WHATSAPP_URL } from "@/shared/lib/whatsapp"

export function SiteFooter() {
  return (
    <footer className="bg-primary text-secondary">
      <div className="mx-auto flex max-w-300 flex-col gap-7 px-4 pt-10 pb-6 md:px-8">
        <div className="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-6 text-[15px]">
          <div className="flex flex-col gap-2">
            <span className="text-[22px] font-bold text-primary-foreground">بيت الكباش</span>
            <span>أضاحي من مزرعتنا لبيتك.</span>
          </div>
          <div className="flex flex-col gap-2">
            <strong className="text-primary-foreground">تواصل معنا</strong>
            <span>
              تليفون: <span dir="ltr">[رقم الهاتف]</span>
            </span>
            <span>العنوان: [عنوان المزرعة]</span>
          </div>
          <div className="flex flex-col gap-2">
            <strong className="text-primary-foreground">مواعيد العمل</strong>
            <span>[الأيام]</span>
            <span>[الساعات]</span>
          </div>
          <div className="flex flex-col items-start gap-2">
            <strong className="text-primary-foreground">عندك سؤال؟</strong>
            <Button variant="whatsapp" size="lg" asChild>
              <a href={WHATSAPP_URL} target="_blank" rel="noreferrer">
                <MessageCircle />
                كلّمنا على واتساب
              </a>
            </Button>
          </div>
        </div>
        <div className="border-t border-primary-foreground/15 pt-4 text-sm">© 2026 بيت الكباش</div>
      </div>
    </footer>
  )
}
