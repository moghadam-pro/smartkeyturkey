# 30-day delivery roadmap

Dates are calendar days starting on 2026-07-19. Each day ends with a meaningful commit when source-controlled work changed, plus a Slack update containing completed work, evidence, blockers, and the next task.

| Day | Date | Focus | Required output |
|---:|---|---|---|
| 1 | 2026-07-19 | Foundation | Charter, Slack channel, roadmap, credential policy, repository setup |
| 2 | 2026-07-20 | Infrastructure audit | WordPress/PHP/database/memory/REST/plugin/backup/staging report |
| 3 | 2026-07-21 | Baseline | URL, content, analytics, SEO, performance, redirect and migration inventory |
| 4 | 2026-07-22 | B2C research | Property personas, objections, trust needs and journey map |
| 5 | 2026-07-23 | B2B research | Procurement personas, qualification fields and inquiry journey |
| 6 | 2026-07-24 | Benchmark | Competitor matrix, reusable patterns, risks and opportunities |
| 7 | 2026-07-25 | Positioning | Value propositions, CTA hierarchy, claims policy and KPIs |
| 8 | 2026-07-26 | Information architecture | Sitemap, navigation, URL patterns and cross-linking |
| 9 | 2026-07-27 | Data model | CPTs, taxonomies, fields, relationships and CSV templates |
| 10 | 2026-07-28 | English SEO foundation | Slugs, canonical rules, metadata, schema and future-localization constraints |
| 11 | 2026-07-29 | Design foundations | Light/dark tokens, type, spacing, grid, motion and breakpoints |
| 12 | 2026-07-30 | Components | Navigation, cards, filters, forms, states and accessibility behavior |
| 13 | 2026-07-31 | Acquisition wireframes | Home, properties, property detail, city and contact flows |
| 14 | 2026-08-01 | B2B/content wireframes | Products, attractions, editorial, search, about and 404 flows |
| 15 | 2026-08-02 | Visual prototype | Responsive light/dark and LTR/RTL priority templates approved |
| 16 | 2026-08-03 | Theme scaffold | Installable custom theme, standards, safe config and documentation |
| 17 | 2026-08-04 | Content-model plugin | CPTs, fields, validation, capabilities, REST and WPML config |
| 18 | 2026-08-05 | Frontend foundation | Theme switcher, fonts, RTL primitives and accessibility |
| 19 | 2026-08-06 | Elementor foundation | Globals, Theme Builder templates, sections, forms and WPML registration |
| 20 | 2026-08-07 | Property experience | Archive, filters, detail, city relationship and inquiry flow |
| 21 | 2026-08-08 | Petrochemical experience | Catalog, filtering, product detail and qualified RFQ flow |
| 22 | 2026-08-09 | Editorial experience | City, attractions, news/laws/events, sources and freshness metadata — attraction foundation completed early on 2026-07-21 |
| 23 | 2026-08-10 | Localization-ready audit | Verify that English templates and data can support a future multilingual phase |
| 24 | 2026-08-11 | AI content system | Source policy, templates, prompts, checks and English pilot records |
| 25 | 2026-08-12 | Data import | Reversible property/product import and pending-review translations |
| 26 | 2026-08-13 | SEO implementation | Metadata, schema, sitemaps, hreflang, redirects and internal links |
| 27 | 2026-08-14 | Performance/security | Core Web Vitals, caching, hardening, backups and secret scans |
| 28 | 2026-08-15 | Full QA | Devices, browsers, languages, themes, forms, accessibility and defects |
| 29 | 2026-08-16 | Launch preparation | Analytics, consent, conversions, Search Console, rollback and smoke test |
| 30 | 2026-08-17 | Launch/growth | Production release, monitoring and 30/60/90-day growth backlog |

## WPML infrastructure gate

Before WPML is installed on staging, verify:

- WordPress 6.0 or newer
- PHP 7.4 through 8.5
- MySQL 5.6+ or MariaDB 10.1+ with `utf8mb4`
- WordPress memory of at least 128 MB; 256 MB preferred
- Working REST API and database table creation permissions
- PHP `mbstring`, SimpleXML and libxml 2.7.8+
- PHP `eval()` is not disabled
- Current Elementor/WPML known issues are checked before version freeze
- Custom post types, taxonomies, fields, strings and custom Elementor widgets are explicitly registered for translation

## Release gates

1. Production-only work is permitted by owner decision from 2026-07-20. Every mutation must be small, recorded, immediately smoke-tested and covered by the active Hetzner seven-day backup rotation.
2. No bulk content generation before English pilot records pass factual and structural review.
3. No development of priority templates before mobile-first wireframe approval.
4. No launch with critical defects or unverified lead delivery.
5. AI-translated legal, financial and regulatory content remains visibly pending review until approved.

## Production-only operating decision

The owner decided not to use the newly created staging site and to continue on production. The staging hostname and database must not receive production data. Work on production follows `docs/PRODUCTION-CHANGE-PROTOCOL.md` until this decision changes.

## English-only phase decision

Effective 2026-07-20, phase one is English-only. WPML and String Translation are outside the active scope. Data models, design tokens and templates should remain localization-ready, but translation, RTL, language switchers and localized URLs are deferred to a later owner-approved phase.

## Launch indexing gate

Search-engine indexing was temporarily discouraged on 2026-07-20 while the production site is incomplete. Re-enabling indexing and verifying robots/meta directives are mandatory launch tasks.
