# SmartKeyTurkey

Public build log and source repository for the SmartKeyTurkey corporate website at [smartkeyturkey.com](https://smartkeyturkey.com/).

SmartKeyTurkey combines two commercial journeys:

- B2C property discovery in Turkey, initially focused on Istanbul, Ankara, Izmir and Antalya.
- B2B petrochemical product discovery and qualified RFQ workflows.

The site also covers Turkish attractions and editorial content about relevant laws, market news and events. SmartKeyTurkey works directly with properties and projects under its control. In petrochemicals it acts as an authorized sales representative and does not present itself as the manufacturer.

## Technology

- WordPress with Hello Elementor and Elementor Pro
- Site-owned `SmartKey Core` plugin for structured content and business logic
- English-first content for phase one
- Planned WPML support for Persian, Arabic and Turkish
- System-aware light and dark themes
- Brand primary color: `#84c341`
- Roboto, Inter and Vazir typography families

## Current progress

Updated: 24 July 2026

- [x] Project charter, 30-day roadmap and production-change protocol
- [x] Initial B2C property and B2B petrochemical audience research
- [x] Property and B2B sourcing competitor benchmark with reusable patterns, risks and implementation priorities
- [x] WordPress baseline audit and brand-asset inventory
- [x] English-only phase-one information architecture
- [x] Petrochemical data model and controlled source-import workflow
- [x] Custom `SmartKey Core` plugin v1.5.0
- [x] Structured petrochemical product type, taxonomy and metadata
- [x] WordPress dashboard overview, content-view tracking, per-product RFQ counters and internal notes
- [x] 99-product catalog imported and published with authorized source images
- [x] Petrochemical RFQ form pilot
- [x] Initial English property-market editorial content
- [x] Rank Math product titles, descriptions, taxonomy and XML sitemap foundation
- [x] Responsive Elementor product archive and single-product templates
- [x] Context-aware petrochemical RFQ with automatic product/grade prefill
- [x] Property content model and phase-one city taxonomy
- [x] Property archive, city and single-property templates
- [x] Secure property consultation form foundation
- [x] SmartKey administration hub and four published, clearly labeled sample properties
- [x] Direct-control sale/rent property model, lifecycle badges, map fields and request-only terms
- [x] Central design tokens with automatic system-aware light/dark mode
- [x] Conversion-focused English homepage built with Elementor and dynamic SmartKey Core content
- [x] Homepage SEO metadata, canonical URL and site-wide search indexing enabled after QA
- [x] Lighthouse accessibility/SEO QA and live property/RFQ submission-path tests
- [x] Global responsive header and footer foundation
- [x] Compact `SmartKeyTurkey` naming and approved SVG wordmark rollout
- [x] Google Site Kit connection and measurement configuration audit
- [x] RFQ integration and application-level database-storage QA
- [x] Database-only property and petrochemical request center (email disabled)
- [x] About Us, contact-rich footer and custom responsive 404 experience
- [x] Sticky product-family sidebar, refined inquiry forms and expanded brand palette
- [x] Dashboard note completion checkboxes with strikethrough state
- [x] Turkey attractions content model, archive, city filters and four sourced pilot guides
- [x] First-party GA4-ready property and petrochemical lead events
- [x] News, laws and events taxonomy, source/review workflow and responsive editorial templates
- [x] Accessible mobile navigation, corrected contact address and consistent responsive footer
- [x] Update-safe global hover colors and compact two-column petrochemical RFQ layout
- [x] Second sourced editorial batch covering the Ceyhan polypropylene project and COP31 Türkiye
- [x] Private Telegram operations bot for request alerts, guided property creation, photo intake and publication status
- [x] Telegram worker deployment runbook with server-only secrets and numeric-user authorization
- [ ] Analytics conversion-event QA
- [ ] Accessibility, performance, security and launch QA
- [ ] WPML implementation after phase-one English approval

## Repository map

| Path | Purpose |
| --- | --- |
| `wp-content/plugins/smartkey-core/` | Site-owned WordPress functionality |
| `data/` | Reviewed product source data and safe import artifacts |
| `docs/` | Research, decisions, runbooks, audit notes and change logs |
| `scripts/` | Reproducible data preparation and workbook generation |
| `outputs/` | Reviewable non-secret project deliverables |

## Delivery workflow

Each meaningful implementation step receives a focused commit and a matching project update. The README, WordPress change log and Slack progress report are updated as the project advances.

Production changes are introduced in controlled batches and verified before the next step. Content may remain in draft or no-index states while layouts, claims, technical data and conversion paths are being reviewed.

## Security and publication policy

This public repository contains only custom source code, documentation, configuration examples and safe content artifacts. It must never contain:

- Passwords, authentication tokens or API keys
- WordPress salts, license keys or production configuration
- Database dumps, private uploads or personal data
- Server access details or backup credentials

See [Project Charter](docs/PROJECT-CHARTER.md), [Roadmap](docs/ROADMAP.md), [Experience Benchmark](docs/BENCHMARK-2026-07-24.md), [SEO Foundation](docs/SEO-FOUNDATION.md), [Production Change Protocol](docs/PRODUCTION-CHANGE-PROTOCOL.md) and [WordPress Change Log](docs/WORDPRESS-CHANGE-LOG.md).

## Content provenance

Petrochemical product information and images are used with authorization from the source owner. Product availability, current specifications, pricing, compliance and final commercial terms must be confirmed for each inquiry.

## SmartKey Core dashboard

Version `1.5.0` includes four site-administration widgets, the public property and petrochemical experiences, and the private Telegram operations workflow:

- Core overview: latest product update, published products, product families, recorded content views and RFQ totals
- Product views and requests: per-product engagement with unassigned RFQ visibility
- Most viewed content: public pages, posts and petrochemical products
- Dashboard notes: short internal notes with a completion checkbox and strikethrough state, stored only for dashboard display

View counts exclude logged-in editors, previews and common crawler user agents, and use a 24-hour browser/content de-duplication cookie. Property inquiries and petrochemical RFQs are stored privately in WordPress under `SmartKey → Requests`; SmartKey-managed forms do not send email.

The product templates use native Elementor Theme Builder records backed by version-controlled SmartKey Core shortcodes. They provide semantic headings, family navigation, responsive product cards, technical data, representative disclosure and an embedded RFQ whose product field is filled from the current product.

The global site chrome adds a branded header, primary navigation, RFQ call to action and a responsive footer. The footer includes the approved Instagram and LinkedIn profiles, the `2012–2026` copyright notice, representative disclosure and linked `Designed and developed by Moghadam.pro` credit.

The Telegram worker uses WordPress APIs and server-side environment variables. It can notify approved operators about stored website requests, guide them through draft property creation with multiple photos, and change property publication status. Real bot tokens and production environment files are never committed.

## License

Unless a file explicitly states otherwise, this repository is published for portfolio and project-transparency purposes. Third-party trademarks, product data, images and WordPress dependencies remain subject to their respective owners' terms.
