# Bait El Kebash — Design Brief
Online sheep / udhiya store (Egypt). Feed this file to Claude Design. Why-notes live in DESIGN_GUIDELINES.md.

## Constraints
- Arabic only, `dir="rtl"`, CSS logical properties (English later, no redesign).
- Mobile first 375px → desktop 1280px.
- Mood: warm, natural, trustworthy. Real animal photos only.
- Western digits for prices/weights. Currency `ج.م`.

## Tokens (proposed — swap when brand exists)
| token | hex | use |
|---|---|---|
| primary | #1F4D3A | buttons, header, active nav |
| primary-soft | #BDD0C6 | selected chip, badge bg |
| accent | #A85431 | price, Eid banner, sale tag |
| bg | #F7F4EE | page background (cream) |
| surface | #FFFFFF | cards, sheets |
| border | #D0BFA9 | dividers, inputs |
| text | #2A1A14 | body |
| muted | #585850 | secondary text |
| whatsapp | #25D366 | WhatsApp button only |

Font: IBM Plex Sans Arabic 400/600/700. Body 16px, H1 28/36px (mobile/desktop).
Radius 16px cards, 12px buttons, full chips. Spacing 4px scale. Touch target ≥44px.

## Components
- **Animal card**: photo 4:3 → badge "فحص بيطري" over photo → name/breed → weight range `35–40 كجم` + age → price (accent, largest text) → add button. Heart icon top corner. Sold out = grey overlay + label.
- **Category chips**: horizontal scroll: الكل · خراف · ماعز · عجول.
- **Eid banner**: green block, "اطلب قبل ___", photo of sheep, one CTA.
- **Trust strip**: 4 icons + label: فحص بيطري · مجزر مرخص · توصيل يوم العيد · دفع عند الاستلام.
- **Steps strip** (home): 3 steps: اختار — حدد التقطيع — استلم.
- **Cut options**: big selectable cards (كاملة · أنصاف · أرباع · قطع صغيرة) with icon.
- **Step indicator**: 3 dots/labels at top of order flow.
- **WhatsApp FAB**: bottom-start corner, every page.
- **Bottom nav (mobile)**: الرئيسية · الأقسام · السلة · طلباتي · حسابي.
- **Status pill**: pending=sand, processing=primary-soft, completed=primary, cancelled=muted. (Backend values.)
- States: skeleton cards, empty cart, error + retry.

## Pages (section order)
1. **Home**: header (logo, search, cart) → Eid banner → category chips → trust strip → "الأكثر طلبًا" card grid → 3 steps → footer (phone, address, hours, WhatsApp).
2. **Listing**: chips + filter (weight, price) → 2-col grid mobile / 4-col desktop.
3. **Product**: photo gallery (thumbs) → name, badges → weight / age / breed as 3 info tiles → "يشمل: الرأس، الكرشة، الذبح والتقطيع" → price + sticky bottom CTA.
4. **Order**: ① cut options → ② address + delivery day (أول/تاني/تالت يوم العيد) → ③ summary + pay (COD) → confirm.
5. **My orders**: list rows: photo, name, date, status pill.
6. **Auth**: phone → OTP code (phone verification exists in API).

## Data available (don't invent fields)
Product: `name, description, category, unit_price, unit, image`.
Planned: `weight_min/max, age, breed, images[], is_available, cut options`.
Order status: `pending, processing, completed, cancelled`.

## References (look at image only when that page needs it)
| # | ref | take | image |
|---|---|---|---|
| 1 | [Qurbani Livestock Marketplace](https://dribbble.com/shots/27457390) | **Main ref.** Green palette, card, product info tiles, checkout summary, payment chips | `docs/design-references/1-qurbani.png` |
| 2 | [Bequrban](https://dribbble.com/shots/21816429) | Card with "Guaranteed Healthy" badge + weight range, category chips, dark photo hero | `docs/design-references/2-bequrban.png` |
| 3 | [Al-Ezz Halal Meat Shop](https://dribbble.com/shots/27721486) | Desktop home: top info bar, WhatsApp CTA in header & hero, trust strip under hero | `docs/design-references/3-al-ezz.png` |
| 4 | [PickPlant RTL](https://dribbble.com/shots/26470506) | Arabic RTL layout: search, chips, card, bottom nav | `docs/design-references/4-pickplant-rtl.png` |
| 5 | [Greens & Grains](https://dribbble.com/shots/25042844) | Cream bg + "3 simple steps" section only (skip the illustrations) | — |

## Avoid
Dark luxury butcher look (e.g. [Rustic Ridge](https://dribbble.com/shots/25270106)) · cartoon/illustrated animals · stock or AI sheep · popups · auto carousels · fake urgency · raw meat / slaughter close-ups on the sheep store pages.
