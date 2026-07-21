# SmartKey property data model

Status: content model and templates implemented; four labeled sample listings published

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
| Location | City taxonomy, public district, latitude and longitude |
| Commercial | Transaction type (sale/rent), listing status (available/sold/rented) and request-only terms |
| Configuration | Property type, rooms, bathrooms, floor, parking and furnished status |
| Area | Gross area and net area |
| Construction | New-build flag, construction year, calculated building age and amenities |
| Project | Developer, payment terms and delivery date |
| Readiness | Completion status and title status |
| Review | Verification status, last-reviewed date and source |
| Compliance | Citizenship review status and direct-control disclosure |

## Published sample set

Four internal demonstration records now exercise the complete data model:

| City | Sample record | Type | Configuration |
| --- | --- | --- | --- |
| Istanbul | Istanbul Urban Residence | Apartment | 2+1, 2 bathrooms, 115/92 m² gross/net |
| Ankara | Ankara City Apartment | Apartment | 3+1, 2 bathrooms, 145/118 m² gross/net |
| Izmir | Izmir Coastal Residence | Residence | 2+1, 2 bathrooms, 125/98 m² gross/net |
| Antalya | Antalya Lifestyle Apartment | Apartment | 1+1, 1 bathroom, 82/67 m² gross/net |

These are published demonstration records containing invented sample values and are clearly labeled as samples. They are not offers or evidence about a real property and must be replaced with source-reviewed data before launch.

## Claims and publication rules

- SmartKeyTurkey works directly with properties and projects under its control. The exact legal capacity and supporting documents must be available for internal review.
- No property price is published. Commercial terms are supplied only after a qualified request.
- Availability, title status and physical specifications require a recent source and review date.
- Citizenship suitability is never inferred from price alone and must remain a review status until confirmed by qualified professionals.
- Sample or pending-review listings must not imply clean title, guaranteed return, residence eligibility or citizenship eligibility.
- Published pages must state that transaction eligibility, restrictions, encumbrances and final terms require case-specific verification.

## Next implementation step

Prepare a controlled pilot listing with reviewed source evidence, then test the complete inquiry email journey and conversion event without publishing unverified commercial or legal claims.
