# Build the store: all screens + API

Build the customer store in `client/apps/store`: every screen from the design, connected to the Laravel API. Work through the phases in order. Run `pnpm --filter store typecheck` and `pnpm --filter store build` after each phase and fix errors before moving on. Don't commit.

## Sources (read only what each phase needs)
- `client/CLAUDE.md`: project rules. Follow it.
- `docs/design/<Board>.dc.html`: one board per screen. Read a board only in the phase that builds it. Boards are visual references (layout, copy, spacing, states), not code to port. Never read `Tokens.dc.html`; the tokens are already in `packages/ui/src/styles/globals.css`.
- `server/API_DOCUMENTATION.md`: API contract. It may be stale, so the source of truth is `server/routes/api.php` plus the matching controllers, form requests and API resources under `server/app/`. Read those for the exact paths, request fields, response shape (`data` wrapper or not), pagination format and status values. Where the code and the doc disagree, the code wins.

## Hard rules

### 1. shadcn only
- Every UI element is a shadcn component from `@workspace/ui/components/*`. No hand-rolled buttons, inputs, cards, badges, sheets, dialogs, selects, chips or skeletons, and no other UI library.
- Add components from `apps/store`: `pnpm dlx shadcn@latest add <name>`. `components.json` routes them into `packages/ui`, with RTL already enabled.
- Expected set: `button card badge input label textarea select field input-otp sheet dialog drawer toggle-group radio-group checkbox separator skeleton sonner breadcrumb aspect-ratio carousel avatar dropdown-menu navigation-menu alert empty spinner scroll-area tabs`. Add more only if a board needs it.
- Match the design through shadcn's own mechanisms, in this order:
  1. the theme tokens (already set)
  2. the `variant`/`size` props
  3. a new `cva` variant added inside the component file in `packages/ui`, e.g. Button `whatsapp`, Badge `status-pending`/`processing`/`completed`/`cancelled`, Button size `lg` = 48px, `xl` = 52px
  4. a `className` override, only for layout (spacing, grid, width)

  Never restyle colours or radius per use.
- Feature components only compose shadcn primitives (e.g. `ProductCard` = `Card` + `AspectRatio` + `Badge` + `Button`). The only raw HTML allowed is layout wrappers (`div`/`section`/`main`/`nav`/`ul`) and `img`.
- Icons: `lucide-react` only. Replace the boards' inline SVGs with the closest lucide icon. Note: lucide has no sheep icon, so the brand mark stays an inline SVG in `shared/components/logo.tsx`.

### 2. Feature-based structure
```
apps/store/src/
  app/                    # app shell only
    main.tsx  router.tsx  providers.tsx   # QueryClientProvider, Toaster, CartProvider
    layouts/app-layout.tsx  layouts/require-auth.tsx
  features/
    products/   api.ts  queries.ts  types.ts  components/  pages/
    cart/       store.tsx  components/
    orders/     api.ts  queries.ts  types.ts  schemas.ts  components/  pages/
    auth/       api.ts  queries.ts  types.ts  schemas.ts  components/  pages/
    home/       pages/  components/
  shared/
    api/client.ts         # fetch wrapper + ApiError
    components/           # header, dock, logo, error states (compose shadcn)
    lib/format.ts         # price(), dates
    types/api.ts          # Paginated<T>, ApiError shape
```
- One feature never imports another feature's internals. Cross-feature use goes through that feature's `index.ts` (public exports only). `shared/` imports from no feature.
- Pages live in their feature. `app/router.tsx` lazy-imports them (`lazy: () => import(...)`).
- Delete the scaffold (`App.tsx`, the `components/` folder and `theme-provider`; no dark mode). Point `main.tsx` at `app/`.
- Path alias `@/` = `src/` (already configured).

### 3. TanStack Query for all server state
- Each feature's `queries.ts` holds:
  - a key factory, e.g. `productKeys = { all: ['products'], list: (p) => [...all, 'list', p], detail: (id) => [...all, 'detail', id] }`
  - `queryOptions(...)`/`infiniteQueryOptions(...)` factories (e.g. `productListOptions(params)`, `productOptions(id)`)
  - thin hooks over those factories (`useProducts`, `useProduct`)
  - mutations (`useCreateOrder`, `useLogin`, `useRegister`, `useLogout`) with `onSuccess` invalidation: a created order invalidates `orderKeys.all`; login and logout reset `['me']`
- `api.ts` holds plain async functions only (no React). Components never call `fetch` or `api.ts` directly; they always use the hooks.
- The `QueryClient` defaults are `staleTime: 60_000` and `retry: (n, e) => !(e instanceof ApiError && e.status < 500) && n < 2`.
- Listing uses `useInfiniteQuery` for load-more, or `placeholderData: keepPreviousData` if the API pages by number. Filters live in URL search params, and those params feed the query key.
- Add `@tanstack/react-query-devtools` as a dev dependency, rendered in dev only.
- The cart is client state, not server state: React context + localStorage in `features/cart/store.tsx`. The API has no cart.

## Stack
React 19, Vite, Tailwind v4, shadcn (`@workspace/ui`), TanStack Query, `react-hook-form` + `zod` (with the shadcn `field` component), `lucide-react`. Add only `react-router` and `@tanstack/react-query-devtools`. Use `fetch`, not axios.

## Phase 0: foundation
1. `pnpm --filter store add react-router` and `pnpm --filter store add -D @tanstack/react-query-devtools`.
2. Add every shadcn component in the expected set. Then add the custom variants listed in Hard rule 1.
3. `shared/api/client.ts`: `fetch` wrapper.
   - Base URL is `import.meta.env.VITE_API_URL ?? "http://localhost:8000/api"`, with `Accept: application/json` on every request.
   - Send `Bearer` from `localStorage.auth_token` when it exists.
   - Unwrap `data` if the API wraps responses.
   - Throw `ApiError {status, message, errors}`.
   - On 401, clear the token and redirect to `/login?next=`.
   - Add `apps/store/.env.example` with `VITE_API_URL` and `VITE_WHATSAPP_NUMBER`.
4. Feature `types.ts` files match the real API resources. Mark design-only fields as optional with `// TODO(api)`: `breed`, `weight_min`, `weight_max`, `age`, `unit`, `images[]`, order `cut`, order `delivery_day`.
5. Write each feature's `api.ts` and `queries.ts` (Hard rule 3), `features/cart/store.tsx`, and `shared/lib/format.ts`. `price(n)` returns `17,800 ج.م` with Western digits. Add a status → {label, icon, badge variant} map; read the server for the status values.
6. `index.html`: `lang="ar" dir="rtl"`, Arabic title.
7. App shell: `app/providers.tsx`, `app/router.tsx`, `RequireAuth`.

   | Route | Screen | Auth |
   |---|---|---|
   | `/` | Home | |
   | `/products` | Listing | |
   | `/products/:id` | Product | |
   | `/checkout` | Order | yes |
   | `/orders` | Orders | yes |
   | `/orders/:id` | Order detail | yes |
   | `/login` | Auth | |
   | `*` | 404 | |

   Add a route `errorElement`.

## Phase 1: shell (boards: Header, Dock, AnimalCard)
- `shared/components/header.tsx`: desktop (`NavigationMenu`, search `Input`, cart `Button` + `Badge`, WhatsApp `Button` variant) and mobile (back `Button`, title, cart). Search submits to `/products?search=`.
- `shared/components/dock.tsx`: mobile bottom nav (`md:hidden`, CSS only) plus the WhatsApp FAB. Link is `https://wa.me/${VITE_WHATSAPP_NUMBER}`.
- `features/products/components/product-card.tsx`: built from the AnimalCard board. Includes the sold-out state, a favourite toggle (`Toggle`, local state only), and add-to-cart.
- `app/layouts/app-layout.tsx` = Header + `<Outlet/>` + Dock, with mobile bottom padding.

## Phase 2: pages, one board at a time
For each page, read its board, build it with shadcn only, wire it to the feature's hooks, then typecheck. Every query renders loading (`Skeleton`), empty (`Empty`), error (`Alert` + retry `Button` that calls `refetch`) and success states. Take the copy for the error and empty states from `Errors.dc.html`; read it once and build `shared/components/states.tsx`.
1. **Home** (`Main.dc.html`): real products for the featured section. Marketing copy stays static; contact details are `[placeholders]`.
2. **Listing** (`Listing.dc.html`):
   - category `ToggleGroup`, price filter `ToggleGroup`, sort `Select`
   - desktop shows a filter `Card`; mobile shows a filter `Sheet` (side `bottom`)
   - load more via `fetchNextPage`
   - weight filter: no API support, so hide it with `// TODO(api)`
3. **Product** (`Product.dc.html`):
   - `Carousel` gallery that falls back to the single `image_url`
   - `Breadcrumb`
   - add-to-cart
   - a 404 state on `ApiError` 404
4. **Order / checkout** (`Order.dc.html`): cart review plus a form (rhf + zod + shadcn `Field`). Submit with `useCreateOrder`.
   - Map 422 `errors[field]` onto the fields with `setError`.
   - On success: clear the cart, go to `/orders/:id`, show a `toast`.
   - `cut` / `delivery_day` UI (`RadioGroup`) is shown but not sent, marked `// TODO(api)`.
5. **Orders** (`Orders.dc.html`): list plus detail. Status uses the `Badge` status variants with icon + text, never colour alone.
6. **Auth** (`Auth.dc.html`): the design shows phone + OTP, but the API has email/password `POST /login` + `POST /register`.
   - Build what the API supports, styled like the board: a `Card` with `Tabs` (login/register).
   - On success, store the token and set `['me']` in the cache, then redirect to `next`.
   - Logout runs `useLogout`, which calls `queryClient.clear()`.
   - Leave a `// TODO(api): phone OTP flow (InputOTP) per Auth.dc.html` comment.
7. **Errors**: 404 page, route `errorElement`, network-error state.

## Other rules
- Tailwind token classes only, no hex. Logical spacing for RTL (`ms/me/ps/pe/start/end`). Tap targets ≥ 44px.
- Use the board's Egyptian Arabic copy verbatim.
- Don't change `server/`. Collect every API gap in `client/API_GAPS.md` (endpoint, field, screen).
- Finish with a short summary: routes built, shadcn variants added, remaining TODO(api) items, and anything you couldn't match from the design with shadcn.
