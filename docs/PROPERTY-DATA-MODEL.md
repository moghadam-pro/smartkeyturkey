# SmartKey property data model

Status: implemented foundation; listing content and templates pending review

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

## Claims and publication rules

- SmartKey must be described as an intermediary/advisor and never as the property owner.
- Price, availability, title status and physical specifications require a recent source and review date.
- Citizenship suitability is never inferred from price alone and must remain a review status until confirmed by qualified professionals.
- Draft or pending-review listings must not imply verified ownership, clean title, guaranteed return, residence eligibility or citizenship eligibility.
- Published pages must state that transaction eligibility, restrictions, encumbrances and final terms require case-specific verification.

## Next implementation step

Build the responsive property archive, city landing and single-property templates, followed by a qualified property inquiry form and a controlled pilot listing.
