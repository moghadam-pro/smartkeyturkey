# SmartKeyTurkey design tokens

SmartKey Core defines shared frontend colors in `assets/css/design-tokens.css`. Product templates, property templates and the global header/footer consume these variables instead of declaring independent hex values.

## Theme behavior

- Light mode is the default.
- Dark mode activates automatically through `prefers-color-scheme: dark`.
- `color-scheme: light dark` lets browser-native controls follow the same system preference.
- The company primary is `#84c341`; the dark-mode interactive primary is lightened for accessible contrast on dark surfaces.

## Core tokens

| Purpose | Token |
| --- | --- |
| Brand primary | `--skt-primary` |
| Page background | `--skt-bg` |
| Component surface | `--skt-surface` |
| Main text | `--skt-text` |
| Muted text | `--skt-muted` |
| Borders | `--skt-border` |
| Hero and footer surfaces | `--skt-hero`, `--skt-footer` |
| Status feedback | `--skt-success`, `--skt-error` |
| Shape and elevation | `--skt-radius`, `--skt-shadow` |

New managed frontend components must use these tokens. A new literal color requires a documented token rather than a component-local value.
