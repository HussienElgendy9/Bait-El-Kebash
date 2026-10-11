// TODO(api): GET /categories is commented out in routes/api.php, so the list and ids are hardcoded.
export const CATEGORIES = [
  { slug: "all", label: "الكل", id: undefined },
  { slug: "sheep", label: "خراف", id: 1 },
  { slug: "goat", label: "ماعز", id: 2 },
  { slug: "calf", label: "عجول", id: 3 },
] as const

export type CategorySlug = (typeof CATEGORIES)[number]["slug"]

export const categoryBySlug = (slug: string | null) => CATEGORIES.find((c) => c.slug === slug) ?? CATEGORIES[0]
