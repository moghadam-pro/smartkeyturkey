# WordPress production change log

## 2026-07-21 — Property content model foundation

- Updated only the site-owned SmartKey Core plugin from version `0.4.0` to `0.5.0`; no third-party plugin was changed.
- Registered the public, REST-ready `skt_property` content type with archive URL `/properties/`.
- Added hierarchical Property City and Property Type taxonomies.
- Seeded the approved phase-one cities: Istanbul, Ankara, Izmir and Antalya.
- Registered structured fields for reference, district, price/currency, rooms, bathrooms, gross/net area, completion, title status, availability, citizenship review, verification, source, review date and representative disclosure.
- Added City, Reference and Verification columns to the WordPress property administration list.
- Added Properties links to the global header and footer.
- Verified all four city terms exist, the Properties administration section is available and `/properties/` resolves with one H1.

## 2026-07-21 — Global header, footer and social links

- Updated only the site-owned SmartKey Core plugin from version `0.3.1` to `0.4.0`; no third-party plugin was updated, removed, activated or deactivated.
- Replaced the minimal Hello Elementor site header and footer presentation with branded, responsive SmartKey components while retaining the theme's page lifecycle.
- Added primary navigation for Home, Petrochemicals and Insights plus a prominent RFQ call to action.
- Added the approved Instagram and LinkedIn profile links with safe external-link attributes.
- Added `© 2012–2026 SmartKey Turkey. All rights reserved.` and linked `Designed and developed by Moghadam.pro` credit.
- Added a concise footer disclosure identifying SmartKey as an intermediary and authorized sales representative rather than a property owner or product manufacturer.
- Verified the new header and footer render once on the product archive, the prior Hello header is visually suppressed, all requested URLs are exact and the archive retains one H1.

## 2026-07-21 — Elementor product experience and SmartKey Core 0.3.1

- Updated only the site-owned SmartKey Core plugin from version `0.3.0` to `0.3.1`; no third-party plugin was updated, removed, activated or deactivated.
- Implemented the production petrochemical archive and single-product experience through Elementor Theme Builder templates `337` and `339` with version-controlled SmartKey Core shortcodes.
- Added family navigation, a responsive paginated product grid, featured imagery, technical-property tables, commercial inquiry guidance and a visible representative disclosure.
- Embedded the existing `Petrochemical RFQ` form on product pages and reliably populated its product/grade field from the current product using Contact Form 7's shortcode-default mechanism.
- Preserved the prior Elementor data in WordPress options before seeding the managed template content, providing a rollback reference.
- Verified the archive renders one H1, 10 product cards per page and the expected Rank Math metadata.
- Verified a representative single product renders one H1, the correct SEO title, a visible disclosure and an RFQ field prefilled with `Ultra Snow White petroleum jelly`.
- Verified the archive at a 390 × 844 mobile viewport without horizontal document overflow.
- Preserved the site-wide `noindex, nofollow` development state until launch QA and owner approval.

## 2026-07-20 — Petrochemical RFQ pilot

- Created Contact Form 7 form `Petrochemical RFQ` (WordPress post ID `36`).
- Added company, destination, product/grade, quantity, packaging, Incoterm, delivery date, document request, technical requirements, file upload and privacy-consent fields.
- Configured the administrative email template and the optional RFQ attachment.
- Resolved all Contact Form 7 configuration errors shown in the editor.
- Did not embed the form on a public page.
- Did not install, remove, activate, deactivate or update any plugin.
- Required before public use: confirm recipient mailbox, run end-to-end email/attachment test, add anti-spam protection, approve privacy copy and configure conversion tracking.

## 2026-07-21 — Controlled petrochemical catalog import

- Recorded the owner's authorization to republish ChemPortal product information and images.
- Recorded SmartKey Turkey's role as an authorized sales representative and sourcing coordinator, not the manufacturer.
- Installed and activated the custom `SmartKey Core` plugin version `0.1.1`; no existing plugin was updated or removed.
- Registered the `skt_product` post type, product-family taxonomy, structured product metadata and a controlled draft importer.
- Validated a source-reviewed dataset containing 99 products.
- Imported all 99 products as drafts with authorized featured images in controlled batches.
- Import result: 99 created, 0 updated, 0 image errors; importer cursor reached 99/99.
- Verified the WordPress product list reports 99 total items and 99 drafts.
- Technical verification remains intentionally marked as pending, and products remain non-public until the Elementor archive/single templates and content QA are complete.

## 2026-07-21 — Product publication and Elementor template kickoff

- Published all 99 petrochemical products in five controlled WordPress bulk-edit batches.
- Verified the product list reports `Published (99)` with no remaining product drafts.
- Verified the public product archive resolves at `/petrochemical-products/` and product detail URLs are accessible.
- Reviewed the current Hello Elementor fallback archive; a dedicated catalog layout is still required.
- Created draft Elementor Theme Builder records for the petrochemical archive (template ID `337`) and single-product layout (template ID `339`).
- The new Theme Builder records remain drafts and have no display conditions until their responsive layouts and dynamic fields are completed.
- No existing plugin was updated or removed.

## 2026-07-21 — Rank Math product SEO foundation

- Changed the WordPress site title from `SmartKeyTurkey` to `SmartKey Turkey`.
- Added the site tagline `Property and Petrochemical Solutions in Turkey`.
- Configured dedicated single-product and product-archive title patterns and meta descriptions in Rank Math.
- Set Product Families as the primary taxonomy for product breadcrumbs and primary-term selection.
- Added grade, applications, availability and verification custom fields to Rank Math content analysis.
- Kept the default product schema disabled until accurate product-specific structured data can be mapped and validated.
- Confirmed `skt_product` is included in the XML and HTML sitemap settings.
- Found and corrected a stale product sitemap registration by saving a controlled off/on cycle.
- Verified `https://smartkeyturkey.com/skt_product-sitemap.xml` returns HTTP 200 XML and contains 100 URLs: 99 products plus the archive.
- Kept Product Family archives out of the sitemap until they have unique descriptions and approved layouts.
- Verified the archive title and meta description render correctly.
- Verified a single product outputs one H1 and the configured product title pattern.
- Kept the site-wide `noindex, nofollow` development state in place until launch QA and owner approval.
- Configured the approved 512 × 512 `favicon.png` as the WordPress Site Icon.
- Renamed Elementor template `337` to `SmartKey — Petrochemical Product Archive`.
- Renamed Elementor template `339` to `SmartKey — Petrochemical Single Product`.
- Added the responsive, accessibility, conversion and SEO acceptance specification for both templates.

## 2026-07-21 — SmartKey Core dashboard 0.2.1

- Updated the site-owned SmartKey Core plugin from version `0.1.1` to `0.2.1`; no third-party plugin was updated or removed.
- Added the `SmartKey Core Overview` dashboard widget with latest product update, published-product count, product-family count, recorded content views and product-request totals.
- Added the `Product Views & Requests` widget with per-product counts and an unassigned-RFQ total.
- Added the `Most Viewed Site Content` widget for public pages, posts and petrochemical products.
- Added the `SmartKey Dashboard Notes` widget with nonce-protected, capability-checked internal notes displayed only in WordPress administration.
- Added first-party content-view counting that excludes logged-in editors, previews and common crawler user agents and de-duplicates each browser/content pair for 24 hours.
- Added Contact Form 7 integration for the `Petrochemical RFQ` form and its `product-grade` field.
- RFQ analytics store aggregate counters only; no names, email addresses, phone numbers, uploaded files or message contents are copied into SmartKey Core.
- Verified the dashboard reports 99 published products and 13 product families.
- Verified a public product test view increments the overview, product table and most-viewed-content widget.
- Verified an internal note can be added and persists across the plugin update.
- The RFQ hook was structurally verified against the active form; a live email submission was intentionally not sent during this test.
