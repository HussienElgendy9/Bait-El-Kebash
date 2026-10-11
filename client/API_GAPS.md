# API gaps (store)

What the design needs that `server/routes/api.php` + controllers don't provide yet. Each item is marked `// TODO(api)` in `apps/store/src`.

| Endpoint | Field / feature | Screen | Store workaround |
|---|---|---|---|
| `GET /categories` | Route is commented out | Home, Listing (category chips) | Hardcoded list in `features/products/categories.ts` with guessed ids 1–3 (خراف/ماعز/عجول) |
| `GET /products` | `search` filter | Header search → Listing | Param is sent; results filtered client-side over loaded pages |
| `GET /products` | `min_price` / `max_price` filter | Listing (price chips) | Params sent; filtered client-side |
| `GET /products` | `sort` (`price_asc`, `price_desc`, `newest`) | Listing (sort select) | Param sent; sorted client-side (server always returns latest first) |
| `GET /products` | `weight_min` / `weight_max` filter | Listing (weight chips) | Weight filter hidden |
| `ProductResource` | `breed`, `weight_min`, `weight_max`, `age` | AnimalCard, Product, Order summary | Optional in `Product` type; UI hides the row when missing |
| `ProductResource` | `images[]` | Product gallery | Carousel falls back to the single `image` |
| `ProductResource` | `is_available` (sold-out) | AnimalCard, Product | Treated as available when missing |
| `POST /orders` | `cut` | Checkout (التقطيع) | `RadioGroup` shown, value not sent |
| `POST /orders` | `delivery_day` | Checkout (يوم التوصيل) | `RadioGroup` shown and validated, value not sent |
| `POST /orders` | `customer_name`, `phone_number`, `address`, `notes` | Checkout (التوصيل) | Prefilled from `GET /user`, validated, not sent (order uses the account profile) |
| `OrderResource` | `cut`, `delivery_day`, `total` | Orders, Order detail | Total summed from `price_snapshot`; cut/day not shown |
| `POST /auth/otp/send`, `POST /auth/otp/verify` | Phone + OTP login | Auth | Email/password `POST /login` + `POST /register` in a Card with Tabs |
| — | Delivery fee by area | Checkout summary | Shows `[حسب المنطقة]` placeholder |

## Notes
- Laravel paginates products/orders with `{ data, links, meta }`; single resources come wrapped in `{ data }`, while `POST /login`, `/register`, `/orders` return `{ message, user|order, token? }` unwrapped. `shared/api/client.ts` unwraps only the single-key `{ data }` case.
- `unit_price` / `price_snapshot` are decimals serialized as strings (`"17800.00"`).
- `ProductResource` returns `image` (full URL), not `image_url`.
