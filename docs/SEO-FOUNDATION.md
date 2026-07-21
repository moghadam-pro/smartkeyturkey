# SEO foundation

Last updated: 2026-07-21

This document is the minimum SEO acceptance standard for every SmartKeyTurkey implementation stage.

## Current launch state

- WordPress search-engine visibility remains disabled during development and QA.
- Public pages therefore intentionally output `noindex, nofollow` until launch approval.
- The Rank Math sitemap is configured and tested before indexing is enabled.
- Removing the global no-index setting is a launch action, not a routine development task.

## Site identity

- Site title: `SmartKeyTurkey`
- Tagline: `Property and Petrochemical Solutions in Turkey`
- Primary domain: `https://smartkeyturkey.com/`
- Primary brand color: `#84c341`
- The approved 512 × 512 SmartKey favicon is configured as the WordPress Site Icon.

## Petrochemical products

### Titles and descriptions

- Single title pattern: `%title% %sep% Petrochemical Products %sep% %sitename%`
- Single description pattern: `Explore %title% specifications, applications and sourcing details. Request current availability and commercial terms from SmartKeyTurkey.`
- Archive title pattern: `Petrochemical Products & Polymer Grades %page% %sep% %sitename%`
- Archive description: `Explore petrochemical products, polymers and industrial grades available through SmartKeyTurkey. Compare specifications and request current commercial terms.`
- Primary taxonomy: Product Families
- Rank Math content-analysis fields: `skt_grade`, `skt_applications`, `skt_availability_status`, `skt_verification_status`

### Sitemap

- Product sitemap: `https://smartkeyturkey.com/skt_product-sitemap.xml`
- Verified response: HTTP 200 with XML content
- Verified inventory: 100 URLs (99 products plus the product archive)
- Product-family archives remain excluded until they receive unique descriptions, useful internal navigation and an approved archive layout.

### Structured data

Default Rank Math schema remains `None` for `skt_product` during the current phase. Generic Article schema would misclassify product pages, while Product schema should not be enabled until required commercial and product identity fields can be mapped accurately. Schema will be introduced as a tested, site-owned implementation after the single-product template and data review.

## Required checks for every stage

1. Use one descriptive H1 and a logical H2/H3 hierarchy.
2. Provide unique titles and descriptions for indexable landing pages.
3. Use descriptive image alternative text without keyword stuffing.
4. Preserve clean, stable URLs and avoid duplicate routes.
5. Add contextual internal links to the relevant business journey, city, family or RFQ page.
6. Keep unreviewed, duplicate, thin, filtered and utility pages out of the sitemap and index.
7. Include visible direct-control or authorized-representative disclosures appropriate to the relevant property or petrochemical journey.
8. Validate responsive layout, keyboard access, contrast and Core Web Vitals impact.
9. Test canonical, robots, Open Graph and sitemap output after template or routing changes.
10. Record the SEO impact and verification evidence in the WordPress change log and README milestone list.

## Properties

- Single title pattern: `%title% %sep% Properties in Turkey %sep% %sitename%`
- Archive title: `Properties in Turkey %page% %sep% %sitename%`
- Primary taxonomy: Property Cities
- Property post type is included in XML and HTML sitemaps.
- Empty city terms remain outside the taxonomy sitemap until approved listings give each landing page substantive inventory.
- Default schema remains unset until listing identity, pricing, availability and offer semantics can be validated per property.

## Launch gate

Before enabling indexing:

- finish the header, footer, archive and single templates;
- approve product claims and representative disclosures;
- add unique content to indexable taxonomy and city pages;
- confirm the approved site icon and add a dedicated social-sharing image;
- validate canonical URLs and structured data;
- run broken-link, accessibility and performance checks;
- confirm the sitemap inventory;
- remove WordPress global no-index only after owner approval;
- submit the final sitemap to the connected search-engine properties.
