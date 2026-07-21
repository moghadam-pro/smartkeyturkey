# SmartKey Turkey

Public build log and source repository for the SmartKey Turkey corporate website at [smartkeyturkey.com](https://smartkeyturkey.com/).

SmartKey Turkey combines two commercial journeys:

- B2C property discovery in Turkey, initially focused on Istanbul, Ankara, Izmir and Antalya.
- B2B petrochemical product discovery and qualified RFQ workflows.

The site also covers Turkish attractions and editorial content about relevant laws, market news and events. SmartKey acts as an intermediary and authorized sales representative; it does not present itself as the property owner or product manufacturer.

## Technology

- WordPress with Hello Elementor and Elementor Pro
- Site-owned `SmartKey Core` plugin for structured content and business logic
- English-first content for phase one
- Planned WPML support for Persian, Arabic and Turkish
- System-aware light and dark themes
- Brand primary color: `#84c341`
- Roboto, Inter and Vazir typography families

## Current progress

Updated: 21 July 2026

- [x] Project charter, 30-day roadmap and production-change protocol
- [x] Initial B2C property and B2B petrochemical audience research
- [x] WordPress baseline audit and brand-asset inventory
- [x] English-only phase-one information architecture
- [x] Petrochemical data model and controlled source-import workflow
- [x] Custom `SmartKey Core` plugin v0.1.1
- [x] Structured petrochemical product type, taxonomy and metadata
- [x] 99-product catalog imported and published with authorized source images
- [x] Petrochemical RFQ form pilot
- [x] Initial English property-market editorial content
- [ ] Elementor product archive and single-product templates
- [ ] Property content model and city/property templates
- [ ] Header, footer and global responsive design system
- [ ] RFQ integration, email delivery and conversion tracking QA
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

See [Project Charter](docs/PROJECT-CHARTER.md), [Roadmap](docs/ROADMAP.md), [Production Change Protocol](docs/PRODUCTION-CHANGE-PROTOCOL.md) and [WordPress Change Log](docs/WORDPRESS-CHANGE-LOG.md).

## Content provenance

Petrochemical product information and images are used with authorization from the source owner. Product availability, current specifications, pricing, compliance and final commercial terms must be confirmed for each inquiry.

## License

Unless a file explicitly states otherwise, this repository is published for portfolio and project-transparency purposes. Third-party trademarks, product data, images and WordPress dependencies remain subject to their respective owners' terms.
