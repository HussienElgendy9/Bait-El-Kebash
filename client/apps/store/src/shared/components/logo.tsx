// lucide has no sheep icon, so the brand mark stays inline.
export function SheepIcon({ className = "size-6" }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 32 32"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
      className={className}
    >
      <path d="M9 15c-2 0-3.5-1.6-3-3.5.4-1.5 2-2 3-1.5.5-2 2.5-3 4.5-2 1-1.5 3.5-2 5-.5 1.5-1 4-.5 4.5 1.5 2 0 3.5 2 2.5 4 1.5 1 1.5 3.5-.5 4.5-.5 2-3 3-4.5 2-1 1.5-3.5 2-5 .5-1.5 1-4 .5-4.5-1.5-2 0-3-2-2-3.5z" />
      <path d="M12 22v5M20 22v5" />
    </svg>
  )
}

export function LogoMark() {
  return (
    <div className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-secondary text-secondary-foreground">
      <SheepIcon />
    </div>
  )
}
