# Brand asset inventory

## Approved inputs

| Asset | Source format | Intended use |
|---|---|---|
| `assets/brand/skt-logo.svg` | SVG, 576 × 112 viewBox | Primary horizontal brand mark |
| `assets/brand/skt-wordmark.svg` | SVG, 449 × 47 viewBox | Wordmark-only placements |
| `assets/brand/favicon.svg` | SVG, 100 × 100 viewBox | Modern browser favicon and application icon source |
| `assets/brand/favicon-512.png` | Transparent PNG, 512 × 512 | WordPress Site Icon and legacy/app fallbacks |

## Confirmed colors

- Primary green: `#84C341`
- Current dark logo fill: `#383838`

The current SVG files contain fixed fill values. Theme-aware header/footer variants should be derived as controlled assets or implemented with CSS masks; source brand files should remain unchanged.

## Typography direction

- Latin interface and content: Inter or Roboto
- Persian and Arabic: Vazir
- Use locally hosted, licensed webfont files where possible.
- Apply language-aware font stacks with `:lang()` selectors and preserve readable metrics across LTR and RTL layouts.

## Asset handling rules

- Keep original vectors in source control.
- Generate optimized delivery variants during the build; do not repeatedly re-export the originals.
- Retain transparency and avoid rasterizing the primary logo for normal web use.
- Add descriptive alternative text only when the logo conveys information; use empty alternative text when it is redundant with nearby site identity.
- Verify contrast separately in light and dark themes before approving derived variants.

