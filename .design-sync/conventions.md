# Insight M-CRM design system — how to build with it

A shadcn/ui (Radix + Tailwind) component set for a Korean dental/medical CRM. Single accent: **Insight Orange `#FF5B2C`**. White canvas, neutral surfaces, hairline borders, compact density. Keep the orange to points of emphasis (one primary CTA, active nav, one key KPI) — everything else stays neutral.

## Setup and wrapping

- **No global provider is required for styling.** Every component is styled through CSS variables that live in `styles.css` (which `@import`s `_ds_bundle.css`). As long as that stylesheet is loaded, components render on-brand.
- **Context wrappers, only where noted:**
  - `Tooltip` → wrap in `TooltipProvider`.
  - `Sidebar` → wrap in `SidebarProvider`; pass `collapsible="none"` for a static inline sidebar.
  - `Form` → driven by `react-hook-form`: `const form = useForm(); <Form {...form}>…</Form>` with `FormField`/`FormItem`/`FormLabel`/`FormControl`/`FormMessage`.
  - Overlays (`Dialog`, `AlertDialog`, `Sheet`, `Drawer`, `Popover`, `Select`, `DropdownMenu`) are controlled with `open`/`defaultOpen` and compose a `*Trigger` + `*Content`.
- **Gotcha — `Button` and `Badge` are plain function components (no `forwardRef`).** Do **not** nest them in a Radix `asChild` trigger (`<PopoverTrigger asChild><Button/></PopoverTrigger>`) — the ref won't attach and the overlay mispositions. Instead style the trigger itself with `buttonVariants({ variant, size })`, or wrap the child in a native `<span>`.

## Styling idiom

Tailwind utility classes mapped to the Insight tokens via shadcn semantic variables. Use these class families — do not invent new color names:

| Purpose | Classes |
| --- | --- |
| Brand / primary CTA | `bg-primary` `text-primary-foreground` (Insight Orange) |
| Neutral secondary | `bg-secondary` `text-secondary-foreground` |
| Danger | `bg-destructive` `text-white` |
| Muted surface / text | `bg-muted` `text-muted-foreground` |
| Card surface | `bg-card` `text-card-foreground` |
| Borders / fields | `border` `border-input` |
| Radii | `rounded-md` (controls) · `rounded-lg` `rounded-xl` (cards) |

The underlying tokens are CSS variables: semantic ones (`--primary`, `--muted-foreground`, `--border`, `--radius`, …) plus Insight raw tokens (`--primary-500` `#FF5B2C`, `--primary-600` hover, `--data-red`/`--data-orange`/`--data-blue` for KPIs, `--bg-page`, `--bg-subtle`).

**When writing custom inline CSS, the semantic variables are raw HSL triplets** (`--border: 0 0% 90%`), so wrap them: `border: 1px solid hsl(var(--border))`, `color: hsl(var(--muted-foreground))`. Prefer the utility classes above — they already wrap correctly.

## Where the truth lives

- Styling: `_ds/insight-ui/styles.css` and its `@import` of `_ds_bundle.css` (all tokens + utilities).
- Per component: `<Name>.d.ts` (props) and `<Name>.prompt.md` (usage). Components are on `window.InsightUI.*`.

## Idiomatic example

```tsx
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter, Button } from 'insight-ui';

<Card>
  <CardHeader>
    <CardTitle>이번 달 예상 매출</CardTitle>
    <CardDescription>강남점 · 최근 30일</CardDescription>
  </CardHeader>
  <CardContent>
    <div className="text-2xl font-semibold">₩38.2M</div>
    <p className="text-muted-foreground text-sm">전월 대비 +9%</p>
  </CardContent>
  <CardFooter>
    <Button size="sm">리포트 보기</Button>
  </CardFooter>
</Card>
```
