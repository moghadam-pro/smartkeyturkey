# WordPress production change log

## 2026-07-21 — Request center, company pages and UI refinement (SmartKey Core 1.1.0)

- Updated only the site-owned SmartKey Core plugin from `1.0.3` to `1.1.0`; no third-party plugin was updated, removed, activated or deactivated.
- Added the administrator-only `SmartKey → Requests` area. Property inquiries and petrochemical RFQs are stored in WordPress and email delivery is disabled for these two managed forms.
- Verified one clearly labeled property QA request and one petrochemical QA request were stored in the production Requests list.
- Added note completion checkboxes with persistent strikethrough state to the dashboard widget.
- Added `#FD8B00`, `#000009`, `#648381`, `#ebebeb` and `#84c341` as centralized palette tokens, an orange/white text-selection treatment and corrected orange link-hover states.
- Separated header and footer brand treatments; the footer remains monochrome, while the dark header renders the S mark white and K green.
- Added a sticky left product-family sidebar that remains present at mobile widths without horizontal page overflow.
- Normalized petrochemical and property request inputs, 8 px radii and compact, borderless right-aligned submit actions.
- Published an SEO-configured About Us page and linked it from the header and footer.
- Added verified Google Maps address, phone and a subtle vector map pattern to the footer.
- Added a custom responsive 404 page and redesigned homepage hero geometry.
- Verified the product archive at desktop and 390 px widths, both forms, About Us, homepage and 404 behavior on production.

## 2026-07-21 — Accessibility, performance and form QA

- Updated only the site-owned SmartKey Core plugin from version `1.0.0` to `1.0.3`; no third-party plugin was changed.
- Resolved homepage placeholder contrast and accessible-name issues and protected the property submit-button colors from Hello Elementor overrides.
- Replaced Elementor's broad Google Fonts request with approved limited Inter and Roboto weights and removed Contact Form 7 assets from the form-free homepage.
- Lighthouse mobile results for the homepage: Performance 81, Accessibility 100, Best Practices 100 and SEO 100; total blocking time improved from 230 ms to 160 ms.
- Lighthouse Accessibility and SEO reached 100/100 on the tested single product; the tested property page reached 100 Accessibility after the button fix.
- Submitted one clearly labeled QA RFQ for `Ultra Snow White petroleum jelly`; Contact Form 7 reported successful sending and the SmartKey request counter increased from 0 to 1.
- Submitted one clearly labeled QA property inquiry for the Istanbul sample; WordPress redirected to the successful `inquiry=sent` state.
- Application-level email handoff is confirmed. Final delivery into the configured mailbox remains an owner-side mailbox check.
- Remaining performance constraint: the uncached root document measured about 810 ms in the test and the approved webfont request remains render-blocking.

## 2026-07-21 — Conversion-focused homepage

- Updated only the site-owned SmartKey Core plugin from version `0.9.0` to `1.0.0`; no third-party plugin was changed.
- Replaced the temporary coming-soon homepage with a complete English acquisition page built as an Elementor shortcode section.
- Added separate property and petrochemical journeys, proof points, featured properties, product-family links, a three-step trust process, recent insights and final conversion actions.
- Connected homepage content to published properties, product families and posts so new records appear without manual layout duplication.
- Added responsive layouts, semantic heading structure, reduced-motion support and automatic light/dark styling through the central design tokens.
- Preserved the previous homepage content and Elementor data in a reversible WordPress option before migration.
- Completed desktop and 390 px mobile QA with one H1, zero horizontal overflow and valid dynamic sections.
- Configured the homepage SEO title, description, focus phrase and canonical URL.
- Removed the temporary site-wide `noindex, nofollow` after the homepage completion gate passed; verified the homepage now outputs `follow, index` and the sitemap index returns HTTP 200.

## 2026-07-21 — Property controls, publication and unified theming

- Updated only the site-owned SmartKey Core plugin from version `0.8.1` to `0.9.0`; no third-party plugin was updated, removed, activated or deactivated.
- Rebuilt the property model around sale/rent and available/sold/rented states; sold and rented badges now appear on cards and single pages.
- Added the new-build/current-year rule, construction year and age, floor, parking, furnished state, amenities, developer, payment terms, delivery date and map coordinates.
- Removed public prices and price filtering. Property and product commercial terms remain available only on request.
- Replaced property intermediary language with direct-control language while retaining the authorized-sales-representative disclosure for petrochemicals.
- Published four clearly labeled sample properties, one each for Istanbul, Ankara, Izmir and Antalya.
- Added OpenStreetMap presentation, expanded property facts and a sale/rent-aware inquiry flow.
- Centralized all managed frontend colors as design tokens and enabled automatic light/dark mode from the visitor's operating-system preference.
- Improved number, label and helper-text spacing in the SmartKey Overview cards.
- Updated Rank Math property-analysis fields, removed the obsolete price field and enabled XML/HTML sitemap inclusion for non-empty Property Cities.
- Verified the city sitemap returns HTTP 200 and contains all four phase-one city URLs.
- Kept global `noindex, nofollow` unchanged; it will be removed only after the homepage is complete and launch QA passes.

## 2026-07-21 — SmartKey administration hub and property samples

- Updated only the site-owned SmartKey Core plugin from version `0.7.0` to `0.8.1`; no third-party plugin was updated or removed.
- Added a branded `SmartKey` top-level WordPress administration menu in the fourth visible position using the configured SmartKey site icon.
- Grouped Petrochemical Products, Add Product, Product Families, Properties, Add Property, Property Cities and Property Types beneath the SmartKey menu.
- Added an overview screen showing published/draft property totals, product totals, city totals and direct management links.
- Created four complete sample property records for Istanbul, Ankara, Izmir and Antalya as drafts.
- Populated every sample with a reference, district, type, price-status copy, rooms, bathrooms, gross/net area, completion status, title status, availability, citizenship review, source, review date and intermediary disclosure.
- Labeled every title, excerpt, content body and verification field as sample/demo data and explicitly prohibited publication as a real listing.
- Verified WordPress reports four property drafts and zero published properties; the public archive therefore continues to show the honest curated-listing empty state.
- Kept global `noindex, nofollow` unchanged. Per owner instruction it will only be removed after the homepage and its content are complete and launch QA passes.

## 2026-07-21 — Property experience and consultation flow

- Updated only the site-owned SmartKey Core plugin from version `0.6.0` to `0.7.0`; no third-party plugin was updated or removed.
- Created and published managed Elementor Theme Builder records `SmartKey — Property Archive` and `SmartKey — Single Property` backed by version-controlled shortcodes.
- Added the responsive `/properties/` archive, four city cards, empty-state messaging, future listing cards and pagination.
- Added city landing routes for Istanbul, Ankara, Izmir and Antalya with unique, conservative positioning copy.
- Added the single-property hero, structured facts, source content and visible intermediary/due-diligence disclosure.
- Added a first-party consultation form with nonce validation, required consent, honeypot spam protection, sanitization and no database storage of personal inquiry data.
- Inquiry email uses the configured WordPress administration address; a live delivery test remains pending owner-approved mailbox QA.
- Configured Rank Math property title and description patterns, Property Cities as the primary taxonomy and property fields for content analysis.
- Verified the property post type is included in XML and HTML sitemaps. Empty Property City terms remain excluded from their own sitemap until approved listings are associated with them.
- Verified the archive and Istanbul route each render exactly one H1, the archive exposes four city cards and the consultation form contains a nonce and required consent.
- Preserved the site-wide `noindex, nofollow` development state until launch approval.

## 2026-07-21 — Brand normalization, wordmark and Site Kit audit

- Updated only the site-owned SmartKey Core plugin from version `0.5.0` to `0.6.0`; no third-party plugin was updated or removed.
- Standardized the public brand name as `SmartKeyTurkey` without a space across repository content, WordPress posts, metadata, managed options, SEO copy and the site title.
- Replaced the header's text label with the approved SVG typography beside the existing logo.
- Added a restrained monochrome treatment of the logo and approved typography in the footer.
- Verified the archive title, body copy and copyright output contain `SmartKeyTurkey` and no legacy spaced name.
- Verified the SVG wordmark loads at its native dimensions and renders once in both the header and footer.
- Audited Google Site Kit: Search Console is connected to the correct HTTPS property; Analytics code insertion, Enhanced Measurement and plugin conversion tracking are enabled; logged-in users are excluded; PageSpeed Insights is connected.
- Verified exactly one Google Analytics loader is present on the public archive. Optional AdSense and email-report subscription were intentionally left unchanged because they are not launch prerequisites.

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
- Added `© 2012–2026 SmartKeyTurkey. All rights reserved.` and linked `Designed and developed by Moghadam.pro` credit.
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
- Recorded SmartKeyTurkey's role as an authorized sales representative and sourcing coordinator, not the manufacturer.
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

- Set and verified the WordPress site title as `SmartKeyTurkey`.
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
