# Bait El Kebash — Design Guidelines

Notes from reviewing similar sheep / udhiya stores (Zabehaty, Lulu udhiya
pre-orders, noon Minutes & Careem udhiya sections, Zabayeh Al Jazeera, Bengal
Meat Qurbani, Liflyhood & Woollen templates). Mainly about design.

---

## What caught my interest (take it)

### 1. The animal card says everything at a glance
Lulu and Zabehaty cards show **breed, age, weight range, price** before you
click. Nobody has to open a page just to know if a sheep fits their budget.

- Photo → name/breed → weight range (e.g. `35–40 kg`) → price → button.
- Price is the biggest text on the card after the photo.

### 2. Ordering is a few clear steps
Zabehaty's flow: **pick animal → choose cutting & packing → choose delivery**.
Each step is one screen with big tappable options, not one long form.

### 3. Cutting / packing as visual choices
Cuts shown as cards or chips ("whole", "halves", "quarters", "small pieces",
"for fatta") instead of a free-text note. Fewer mistakes, fewer phone calls.

### 4. Eid deadline is visible
noon / Careem / Lulu all show "order before ___". A simple banner with the
deadline (or a countdown in the last week) creates urgency without being pushy.

### 5. What's included is spelled out
Lulu lists: head included, offal included, delivery on day 2–3 of Eid.
State it on the product page so the customer doesn't have to ask.

### 6. Trust signals
"Vet checked", "slaughtered in a licensed slaughterhouse", real farm photos.
In Egypt people buy sheep they can *see* — trust is the whole game.

### 7. Delivery or donate
Several services let you deliver the meat home **or** donate part of it.
Nice option to add later; design the checkout so it can fit.

### 8. Mobile first
Every serious competitor is an app or a mobile-feeling site. Most of our
customers will come from a phone (often from a Facebook / WhatsApp link).

---

## What I didn't like (avoid it)

- **Big-marketplace clutter** (noon / Careem): the udhiya is buried between
  groceries and promos. We're a single-purpose shop — keep it focused.
- **Stock / cartoon sheep images** (template sites). Fake photos kill trust.
  Every product photo should be a real animal from our farm.
- **Farm-blog style templates** (Woollen): pretty, but lots of "about our
  farm" sections and little buying. Selling comes first.
- **Hidden info**: missing weight, unclear what's included, prices only after
  login or a phone call.
- **Long single-page forms** for the order. Split into steps.
- **Promos everywhere**: popups, flashing banners, auto-playing sliders.
- **English-only or broken RTL** — many Gulf apps feel translated, not designed
  for Arabic.

---

## What we must take care of while building

### Language & RTL
- Arabic first, `dir="rtl"`. Use CSS logical properties (`margin-inline-start`,
  `padding-inline-end`) so the layout flips correctly.
- Pick a font with good Arabic glyphs (e.g. Cairo, Tajawal, IBM Plex Sans Arabic).
- Prices and weights: decide once — Arabic numerals (١٢٣) or Western (123) —
  and use the same everywhere. Currency as `ج.م` / `EGP` consistently.

### Product data the design needs
Right now `products` has `name, description, category_id, unit_price, unit,
image`. The cards above need more than that. Consider adding:

| Field          | Why                                   |
|----------------|---------------------------------------|
| `weight_min` / `weight_max` | weight range on the card |
| `age` / `breed` | the first things buyers ask           |
| `images` (multiple) | one photo isn't enough for a live animal |
| `is_available` / stock | "sold out" state instead of a 404 |
| cut options    | the cutting step in the order flow    |

Add these only when the frontend actually uses them.

### Visual style
- Warm, natural palette: off-white / cream background, one earthy accent
  (deep green or terracotta), dark text. One accent color for all buttons.
- Real photos, same background and angle for all animals so the grid looks clean.
- Generous spacing, large touch targets (min 44px), readable 16px+ body text.
- Support dark mode only if it's cheap; light mode done well matters more.

### States people actually hit
- Loading (skeleton cards, not a blank page).
- Empty (no products in category, empty cart).
- Error (failed order, lost connection) with a clear retry.
- Sold out / reserved animal.
- Order status matches our backend: pending → processing → delivered / cancelled.

### Trust & contact
- WhatsApp button visible on every page — that's how Egyptian customers
  ask questions.
- Phone number, location, and working hours in the footer.
- Clear payment options (cash on delivery is expected).

### Accessibility basics
- Alt text on every animal photo (breed + weight).
- Enough color contrast; never use color alone to show status.
- Forms with real labels, not only placeholders.

---

## What we don't want in our project

- ❌ Stock photos or AI-generated sheep.
- ❌ Fake reviews, fake "only 2 left!" counters, fake urgency.
- ❌ Popups on first load (newsletter, app download, etc.).
- ❌ Auto-playing carousels as the main way to show products.
- ❌ Hiding prices behind login.
- ❌ Heavy animations / large JS libraries that make the site slow on mobile data.
- ❌ Features nobody asked for yet (wishlists, loyalty points, chat bots) —
  ship the core buying flow first.
- ❌ Graphic slaughter imagery anywhere on the site.

---

## Core pages (MVP)

1. **Home** — Eid banner + deadline, categories, featured animals, trust strip.
2. **Category / listing** — grid of animal cards, simple filters (weight, price).
3. **Product** — photos, breed/age/weight, what's included, price, order button.
4. **Order steps** — cutting → delivery address & date → confirm (COD).
5. **My orders** — list with status.
6. **Login / register** with phone verification (already in the API).

---

### References
- Zabehaty: https://apps.apple.com/app/id1227769641
- Lulu udhiya pre-order: https://gcc.luluhypermarket.com/en-bh/pre-order-eid-al-adha-sacrifice-udhiya-somali-whole-lamb-12-16-kg/p/1992346/
- Zabayeh Al Jazeera: https://apps.apple.com/us/app/id6449191847
- noon / Careem udhiya coverage: https://www.khaleejtimes.com/business/from-sheep-to-camels-uae-residents-can-now-order-eid-sacrifice-animals-online
- Liflyhood template: https://www.templatemonster.com/website-templates/liflyhood-complete-farm-animal-product-display-responsive-website-template-451499.html
- Woollen template: https://templatemonster.com/wordpress-themes/woollen-organic-sheep-farming-amp-wool-woocommerce-elementor-theme-512078.html
