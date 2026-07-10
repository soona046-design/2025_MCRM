# design-sync NOTES — Insight M-CRM (shadcn/ui set)

Repo-specific gotchas for future syncs. Append a bullet whenever something is learned.

## Setup facts

- This is a **Next.js app** (`m-crm-project/`), not a published component library. There is no `dist/`, no Storybook, and no importable package. We sync the **shadcn/ui primitives** under `m-crm-project/src/components/ui/` (56 files).
- The design system is **synthesized** (synth-entry mode): no build, components discovered by scanning `src/components/ui/*.tsx` for PascalCase exports.
- **`pkg` is a synthetic name `insight-ui`.** Its "package dir" is `.ds-sync/scratch/node_modules/insight-ui/` — a stub that exists ONLY to hold the compiled `styles.css` (cssEntry must live under PKG_DIR). It is NOT committed (`.ds-sync/` is gitignored) and must be regenerated on a fresh clone (see "Re-sync steps").
- `srcDir` and `tsconfig` in config.json are **relative paths** (`../../../../m-crm-project/...`) resolved from PKG_DIR. `@/` alias (`@/lib/utils`, `@/hooks/*`, `@/components/ui/*`) resolves via the repo's own tsconfig `paths` (`@/*` → `./src/*`).
- **macOS NFC/NFD gotcha (critical):** the repo path contains Korean (`인사이트`). `process.cwd()` returns NFD; an absolute config literal is NFC. The converter's workspace-root containment check uses `relative()` which then mismatches and rejects the path as "outside the workspace root" (kills `@/` alias → `Could not resolve @/hooks/...`). **Fix: keep config paths RELATIVE**, so they resolve through the NFD cwd/PKG_DIR and normalization matches. Do NOT switch these to absolute paths.

## Broken repo imports (worked around)

- `toaster.tsx` and `sidebar.tsx` import `@/hooks/use-toast` and `@/hooks/use-mobile`, but those hook files actually live in `src/components/ui/` (use-toast.ts, use-mobile.tsx), not `src/hooks/`. Broken in the repo; never caught because the app uses MUI, not these shadcn files.
- Worked around with a dedicated tsconfig `.ds-sync/scratch/ds-tsconfig.json` (cfg.tsconfig → `../../ds-tsconfig.json`) that adds `@/hooks/use-toast` and `@/hooks/use-mobile` path aliases pointing at the real files, on top of `@/* → src/*`. This tsconfig is NOT committed (gitignored) — recreate on fresh clone.

## Dependencies — scratch install (repo untouched)

- The shadcn components import ~40 packages but only 6 were installed in `m-crm-project` (`react`, `@radix-ui/react-avatar`, `@radix-ui/react-slot`, `class-variance-authority`, `lucide-react`, `recharts`).
- The rest (~34: most `@radix-ui/*`, `cmdk`, `vaul`, `sonner`, `react-day-picker`, `react-hook-form`, `embla-carousel-react`, `input-otp`, `next-themes`, `react-resizable-panels`) are installed into **`.ds-sync/scratch/node_modules`** (React 18.3.1 pinned). `--node-modules` points there so ALL bare imports + React resolve from ONE tree (no duplicate React).
- User approved the scratch-install approach (does not modify their repo). If shadcn is ever adopted in the app itself, install these into `m-crm-project` instead and drop the scratch dir.

## Styling — Tailwind must be compiled

- shadcn styling is Tailwind utility classes. Raw `globals.css` only defines the `:root` Insight tokens; the utilities (`bg-primary`, `rounded-md`, …) don't exist until Tailwind compiles them.
- We compile with the repo's own `tailwindcss@3.4.3` + `tailwind.config.ts`, input `src/app/globals.css`, into `insight-ui/styles.css` (the cssEntry). This CSS carries BOTH the Insight token vars AND every utility the components use.
- `globals.css` maps shadcn HSL vars to Insight colors: `--primary: 13 100% 59%` = Insight Orange `#FF5B2C`, `--radius`, data colors, etc. So the shadcn set renders on-brand.

## Re-sync steps (fresh clone)

1. Re-copy staged scripts (base skill step 7 `cp -r`).
2. Recreate scratch deps: `.ds-sync/scratch` npm install (see Dependencies list above) + `npm i esbuild ts-morph @types/react` in `.ds-sync`.
3. Recreate `.ds-sync/scratch/node_modules/insight-ui/` stub (package.json) and recompile Tailwind → its `styles.css`.
4. Rebuild: `node .ds-sync/package-build.mjs --config .design-sync/config.json --node-modules .ds-sync/scratch/node_modules --out ./ds-bundle`.

## Preview authoring learnings (51 components, 4 subagent batches)

- **`hsl()` wrapping required in preview inline styles.** The shadcn CSS vars in this repo are raw HSL triplets (`--border: 0 0% 90%`, `--muted-foreground: ...`), NOT full colors. Using `color: var(--muted-foreground)` or `border: 1px solid var(--border)` is invalid CSS and silently no-ops (invisible borders, black-instead-of-gray text). Always wrap: `hsl(var(--border))`, `hsl(var(--muted-foreground))`. Exception: ChartContainer's own `--color-*` props are full hex (set by ChartStyle) — use unwrapped.
- **Source bug — `Button`/`Badge` are NOT `React.forwardRef`.** `<SomeTrigger asChild><Button|Badge></...>` (Radix Popover/DropdownMenu/Tooltip/etc.) can't attach the anchor ref → Popper positions content off-screen (verified: content at y:-371, position:static, data-state=open). Real bug in `m-crm-project/src/components/ui/button.tsx` (and badge.tsx) affecting any asChild-trigger usage. Preview workarounds: style the Trigger directly with `buttonVariants({...})`, or wrap the child in a native `<span>` (accepts refs). Suggested source fix: wrap Button/Badge in `React.forwardRef`. Not fixed (out of sync scope; components unused by the MUI app).
- **tailwindcss-animate is NOT in the tailwind plugin list**, so `animate-in`/`fade-in-0`/`zoom-in-95` classes are no-ops (no enter animation). Overlays are not hidden by animation — if one is invisible it's a positioning/ref issue, not timing.
- **Overlay open-state techniques (in previews):** Dialog/AlertDialog/Sheet/Drawer/Select — `open` on Root + `position:static;transform:none` on Content to pull portal content into flow. Popover/DropdownMenu/Menubar/NavigationMenu — Popper-based; keep Radix fixed positioning with a correctly-ref'd trigger (or `forceMount`+`position:absolute`+offset for Menubar/NavMenu). ContextMenu — Radix Root has no `open` prop and only opens on real right-click (static capture can't dispatch) → composed an on-brand static content block as best-effort.
- **Sidebar:** pass `collapsible="none"` for static capture — short-circuits to a plain static div, avoiding offcanvas/mobile-Sheet machinery.
- **Form:** `useForm()` from react-hook-form (resolves from scratch node_modules) + `<Form {...form}>` + FormField/FormItem/etc.
- **Field horizontal:** short CJK labels wrap mid-word — set `whiteSpace:nowrap` on horizontal FieldLabel.

## Known render warns (triaged as legitimate — not new issues)

- **Skeleton renders orange-tinted** (not gray): `Skeleton` uses `bg-accent` and this DS maps `--accent` to Insight orange in globals.css. Intentional per the tokens, not a bug.
- **ContextMenu** static card shows a composed content block (no real right-click in capture).

## Re-sync risks

- **Scratch deps are @latest** (except React 18.3.1). A future scratch install may pull newer Radix/day-picker/etc. that diverge from what the component source targets → possible render regressions. Pin versions here if drift bites.
- The synthetic `insight-ui` package dir and compiled `styles.css` are NOT in git — every fresh clone must regenerate them (steps above) or the build fails on missing cssEntry.
- Component list is derived by scanning src for PascalCase exports → includes compound sub-parts (CardHeader, DialogContent, …) as separate entries. Grouping is flat ('general').
