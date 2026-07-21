# Google Site Kit audit

Audit date: 2026-07-21

## Connected services

| Service | Status | Verification |
| --- | --- | --- |
| Search Console | Connected | Correct `https://smartkeyturkey.com/` property selected |
| Google Analytics 4 | Connected | Site Kit inserts the code snippet |
| Enhanced Measurement | Enabled | Confirmed in the connected Analytics settings |
| Plugin conversion tracking | Enabled | Confirmed in the connected Analytics settings |
| Logged-in user exclusion | Enabled | All logged-in users excluded from Analytics |
| PageSpeed Insights | Connected | Available in the Site Kit dashboard |

The public petrochemical archive was checked and contained one Google Analytics loader. This avoids double-loading the base Analytics tag through the current WordPress configuration.

## Deliberately deferred

- AdSense is not required for either the property or petrochemical lead-generation journeys and remains disconnected.
- Email reports remain optional and were not subscribed without an explicit owner preference.
- Search indexing remains globally disabled during development. Search Console connectivity does not override the launch no-index gate.
- Consent management and regional privacy behavior require a dedicated launch-stage review before advertising or remarketing features are introduced.

## Next measurement work

- Run a real-time Analytics visit test using a logged-out browser.
- Submit test property and petrochemical inquiries after destination mailboxes are approved.
- Verify conversion events and prevent duplicate form events.
- Define approved campaign parameters and a lead-source reporting convention.
