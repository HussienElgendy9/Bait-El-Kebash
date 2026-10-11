# Client

## Architecture
- **shadcn only**: every UI element is a shadcn component from `@workspace/ui/components/*`. Add components with `pnpm dlx shadcn@latest add <name>` run in the app folder; they install into `packages/ui`. Match the design via tokens → `variant`/`size` → a new `cva` variant in `packages/ui` → `className` for layout only. No hand-rolled UI, no other UI libs, icons from `lucide-react`.
- **Feature-based**: `src/app` (router, providers, layouts), `src/features/<name>/{api.ts,queries.ts,types.ts,schemas.ts,components/,pages/,index.ts}`, `src/shared` (api client, shared components, lib). Features import each other only through `index.ts`; `shared/` imports no feature.
- **TanStack Query for all server state**: key factories + `queryOptions` in each feature's `queries.ts`; components use hooks, never `fetch`. Mutations invalidate by key. Cart = client state (context + localStorage).

## Design reference
- Design boards: `../docs/design/<Page>.dc.html` (exported from the claude.ai Design canvas). Read ONLY the board for the page you're building. Don't read other boards unless asked.
- Pages: Main (= Home), Listing, Product, Order, Orders, Auth, Errors. Shared: Header, Dock, AnimalCard. Catalog: Components. Each page board has both desktop and mobile layouts (`m` = width < 768).
- Boards are visual references (layout, copy, values), not code to port; rebuild with shadcn.
- Tokens are already in `packages/ui/src/styles/globals.css`. Use Tailwind token classes (`bg-primary`, `text-price`, `bg-whatsapp`, `bg-primary-soft`, `border-border`, `text-muted-foreground`, `rounded-lg`…), never raw hex. Don't read `Tokens.dc.html` for colors.
- RTL Arabic (`dir="rtl"`, `lang="ar"`): logical utilities (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`). Western digits; price as `17,800 ج.م`.
- Tap targets ≥ 44px. Radius: 16px cards/sheets, 12px buttons/inputs, full for chips/pills.
- API: `server/routes/api.php` + controllers are the source of truth over `server/API_DOCUMENTATION.md`. Fields shown in the design but missing from the API (weight_min/max, age, breed, images; cut + delivery_day on orders; OTP auth): mock and mark `// TODO(api)`, list in `client/API_GAPS.md`.
