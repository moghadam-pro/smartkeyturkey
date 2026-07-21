# WordPress production change log

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
