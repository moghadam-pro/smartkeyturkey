# Standalone plugins

These packages are independent from SmartKeyTurkey and are intended for reuse on other WordPress installations.

## Native Forms

- Source: `standalone-plugins/native-forms/`
- Installable package: `dist/native-forms-1.0.0.zip`
- WordPress menu: `Forms`
- Submenus: `Forms`, `Submissions`
- Shortcode: `[native_form id="123"]`
- Storage: private WordPress submissions; email disabled
- Languages: Unicode-safe content with automatic RTL behavior plus `dir="rtl"` for Persian forms on non-RTL WordPress sites

Native Forms uses its own namespace, post types, metadata keys, shortcode, actions and CSS classes. It can be installed alongside SmartKey Forms without collisions.
