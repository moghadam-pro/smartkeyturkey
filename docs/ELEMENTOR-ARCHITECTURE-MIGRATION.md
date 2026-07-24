# Elementor architecture migration

## Target ownership

| Layer | Owner |
| --- | --- |
| Header, footer, page sections and responsive layout | Elementor Pro Theme Builder |
| Primary and footer navigation items | Native WordPress menus |
| Property and petrochemical content models | SmartKey Core |
| Property/product queries and dynamic business data | Focused SmartKey Core widgets or shortcodes |
| Forms, fields, submissions and response state | SmartKey Forms |
| Telegram property operations | SmartKey Core |
| Telegram submission notifications | Event integration between SmartKey Forms and SmartKey Core |

## Completed foundation

- Added native `SmartKey Primary` and `SmartKey Footer` WordPress menus.
- Connected the transitional PHP header/footer to those menus.
- Created independent SmartKey Forms with form records, field definitions, Elementor-compatible embeds, private submissions, Contact Form 7 RFQ compatibility and provider-neutral notification events.
- Removed request ownership from SmartKey Core.

## Safe Elementor migration order

1. Activate SmartKey Forms and verify existing requests still appear.
2. Deploy SmartKey Core 1.7.0 and verify property/RFQ submissions plus Telegram delivery.
3. Confirm both native WordPress menus in Appearance → Menus.
4. Build and publish the Elementor Header using Site Logo, Nav Menu and Button widgets.
5. Build and publish the Elementor Footer using Image, Nav Menu, Icon List, Social Icons and Text widgets.
6. Disable the transitional SmartKey Core header/footer only after Elementor display conditions are active and visually verified.
7. Rebuild Home as editable Elementor sections with native widgets and Loop Grid where possible.
8. Keep only focused dynamic elements—property, product-family and latest-post queries—as loops or dedicated data widgets.
9. Replace the one-widget `[skt_home]` page after desktop, tablet, mobile, light/dark and SEO-heading checks pass.
10. Repeat the same pattern for About, archives and single templates.

## Why this is staged

Elementor Theme Builder display conditions and the current PHP chrome cannot safely own the same header/footer simultaneously. Publishing Elementor first and disabling the transitional output second prevents duplicate or missing site chrome.

The existing homepage remains online during reconstruction. Its shortcode is a temporary production fallback, not the target architecture.

