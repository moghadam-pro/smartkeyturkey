# Chemportal product extraction report

Date: 2026-07-21

## Result

- Source catalog: https://chemportal.com.tr/?page_id=28
- Products captured: 99
- Leaf categories captured: 13
- Technical property rows captured: 987
- Products with technical property tables: 99
- Products with a distinct applications list: 90
- Products with a source image URL: 99

## Category reconciliation

| Category | Products |
|---|---:|
| HDPE | 39 |
| LDPE | 16 |
| General Chemicals | 11 |
| LLDPE | 11 |
| ABS | 4 |
| Petroleum | 4 |
| GPPS | 3 |
| HIPS | 3 |
| Polypropylene | 3 |
| MDPE | 2 |
| Alcohols | 1 |
| EPS | 1 |
| UREA | 1 |
| **Total** | **99** |

The total reconciles with the source catalog's four top-level counts: Chemicals 12, Petroleum 4, Polymers 82 and UREA 1.

## Content handling

- Product names, categories, applications, property values, units and test methods are treated as factual source data.
- Public-facing short descriptions are original SmartKey wording generated from category/application facts; source prose is not copied into the import file.
- Every row includes the exact product source URL and extraction date.
- Technical data is marked pending review and must be confirmed against current supplier TDS/SDS before publication or quotation.
- Stock, availability, origin, MOQ, Incoterms and compliance are not inferred.

## Image handling

All 99 source image URLs are recorded for identification. Their status is `Pending written permission`; no source image has been copied into the WordPress media library or published by SmartKey. Approved replacements may be supplied by the owner, licensed from the source, or generated as non-product-specific category artwork.

## Deliverables

- `data/petrochemical-products.csv`: WordPress/import-oriented flat file
- `data/chemportal-products-source.json`: structured source facts and property arrays
- `outputs/smartkey-products-2026-07-21/smartkey-petrochemical-catalog.xlsx`: review workbook
- `scripts/parse_chemportal.py`: repeatable HTML-to-data parser
- `scripts/build_product_workbook.mjs`: repeatable workbook builder

## Publication gate

Do not bulk-publish these records until:

1. Manufacturer/supplier attribution is approved.
2. Current TDS/SDS documents are matched to each grade.
3. Technical values receive human review.
4. Image reuse rights or replacement images are approved.
5. RFQ email delivery and attachment handling pass testing.
