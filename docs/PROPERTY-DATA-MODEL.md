# SmartKey property data model

Status: content model and templates implemented; pilot listings pending review

## Content structure

- Post type: `skt_property`
- Public archive: `/properties/`
- City taxonomy: `skt_property_city`
- Type taxonomy: `skt_property_type`
- Phase-one cities: Istanbul, Ankara, Izmir and Antalya

## Structured fields

| Group | Fields |
| --- | --- |
| Identity | Listing reference, title and editorial summary |
| Location | City taxonomy and district |
| Commercial | Price, currency and availability |
| Configuration | Property type, rooms and bathrooms |
| Area | Gross area and net area |
| Readiness | Completion status and title status |
| Review | Verification status, last-reviewed date and source |
| Compliance | Citizenship review status and representative disclosure |

## Draft sample set

Four internal demonstration records now exercise the complete data model:

| City | Sample record | Type | Configuration |
| --- | --- | --- | --- |
| Istanbul | Istanbul Urban Residence | Apartment | 2+1, 2 bathrooms, 115/92 m² gross/net |
| Ankara | Ankara City Apartment | Apartment | 3+1, 2 bathrooms, 145/118 m² gross/net |
| Izmir | Izmir Coastal Residence | Residence | 2+1, 2 bathrooms, 125/98 m² gross/net |
| Antalya | Antalya Lifestyle Apartment | Apartment | 1+1, 1 bathroom, 82/67 m² gross/net |

These are drafts containing invented demonstration values. They are not listings, offers, evidence of availability or claims about a real property. Each must be replaced with source-reviewed data before publication.

## Claims and publication rules

- SmartKey must be described as an intermediary/advisor and never as the property owner.
- Price, availability, title status and physical specifications require a recent source and review date.
- Citizenship suitability is never inferred from price alone and must remain a review status until confirmed by qualified professionals.
- Draft or pending-review listings must not imply verified ownership, clean title, guaranteed return, residence eligibility or citizenship eligibility.
- Published pages must state that transaction eligibility, restrictions, encumbrances and final terms require case-specific verification.

## Next implementation step

Prepare a controlled pilot listing with reviewed source evidence, then test the complete inquiry email journey and conversion event without publishing unverified commercial or legal claims.
