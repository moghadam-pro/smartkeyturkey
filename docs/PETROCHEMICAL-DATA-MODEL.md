# Petrochemical product data model

## WordPress object

Use a dedicated `skt_product` custom post type owned by the future SmartKey core plugin. Elementor will render its archive and single templates, while the data remains portable and available through the WordPress REST API.

## Required fields for publication

| Field | Purpose |
|---|---|
| Product name | Buyer-facing generic name |
| Slug | Stable English URL identifier |
| Product family | Primary catalog filter |
| Grade | Supplier/manufacturer grade identifier |
| Short description | Factual summary without unsupported claims |
| Physical form | Handling and filtering context |
| Applications | Buyer discovery and suitability context |
| RFQ enabled | Controls quotation CTA |
| Verification status | Draft, source-reviewed, supplier-confirmed or archived |
| Source URL | Traceability for imported facts |
| Last reviewed date | Freshness control |
| Intermediary disclosure | Required trust statement |

## Commercial fields

- Manufacturer or supplier name
- Country of origin
- Minimum order quantity and unit
- Packaging options
- Supported Incoterms
- Dispatch location or port
- Availability label and checked date

Commercial values should remain empty or marked **Confirm by RFQ** until supported by current supplier evidence.

## Technical and compliance fields

- CAS number
- HS/GTIP code
- UN number
- Dangerous-goods class
- Key properties as structured name/value/unit/test-method rows
- TDS URL and revision date
- SDS URL and revision date
- CoA availability
- Certificate of Origin availability
- Additional compliance document labels and URLs
- Technical properties as repeatable rows: property, unit, test method and value
- Source image URL and image-rights status

The owner confirmed authorization to republish the Chemportal catalog and its images on 2026-07-21. Image source and authorization provenance remain stored for audit.

## Taxonomies

- `skt_product_family`
- `skt_product_industry`
- `skt_product_application`
- `skt_product_form`
- `skt_document_type`

## URL model

- Catalog: `/petrochemical-products/`
- Product: `/petrochemical-products/{product-slug}/`
- RFQ page: `/request-a-quote/`

## RFQ integration

Every product CTA passes product ID, product name and grade into the RFQ form. The current Contact Form 7 pilot has ID `36` and shortcode:

`[contact-form-7 id="031af98" title="Petrochemical RFQ"]`

The form is not yet embedded on a public page. Email delivery, attachment handling, spam controls, privacy copy and conversion tracking are launch gates.

## Current source inventory

The 2026-07-21 Chemportal extraction contains 99 products across 13 leaf categories and 987 technical-property rows. All records remain marked **technical review pending** and **availability: Confirm by RFQ**.
