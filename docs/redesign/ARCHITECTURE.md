# Redesign Layer Architecture

## Overview

The Anagami ERP frontend redesign is implemented as a **CSS override layer** that loads after all original Anagami ERP styles. This approach:
- Requires zero changes to PHP, controllers, routes, or models
- Modifies only 1 existing file (`main.scss` — 3 lines)
- Is fully reversible (remove the import to revert)
- Survives upstream Anagami ERP updates with minimal merge work

---

## File Structure

```
resources/sass/redesign/
├── redesign.scss                 ← Main entry point (imports everything)
├── _variables-redesign.scss      ← Design tokens (colors, spacing, shadows)
├── _typography-redesign.scss     ← Onest font, size scale, global type
├── _bootstrap-overrides.scss     ← Bootstrap 4 component overrides
├── _mixins-redesign.scss         ← Reusable SCSS mixins
├── _dark-theme-redesign.scss     ← Dark theme overrides
├── components/
│   ├── _sidebar.scss             ← Dark sidebar
│   ├── _header.scss              ← White header
│   ├── _content.scss             ← Content area, filters
│   ├── _cards.scss               ← Card enhancements
│   ├── _buttons.scss             ← Button refinements
│   ├── _tables.scss              ← DataTables styling
│   ├── _forms.scss               ← Form inputs, editors, pickers
│   ├── _modals.scss              ← Modal dialogs
│   ├── _alerts.scss              ← Alert notifications
│   ├── _badges.scss              ← Status badges
│   ├── _dropdowns.scss           ← Dropdown menus
│   ├── _pagination.scss          ← Pagination controls
│   └── _tabs.scss                ← Tab navigation
└── pages/
    ├── _dashboard.scss           ← Dashboard page
    ├── _login.scss               ← Login/register
    ├── _invoices.scss            ← Finance pages
    ├── _projects.scss            ← Project/task pages
    └── _settings.scss            ← Settings pages
```

---

## Compilation Pipeline

```
resources/scss/main.scss          ← Anagami ERP's main entry point
  ├── @import "variables"          (original Anagami ERP variables)
  ├── @import "bootstrap"          (Bootstrap 4)
  ├── @import ...                  (all original Anagami ERP styles)
  ├── @import "dark-theme"         (original dark theme)
  └── @import "redesign/redesign"  ← REDESIGN LAYER (loaded last!)
        ├── _variables-redesign    (new design tokens)
        ├── _mixins-redesign       (helper mixins)
        ├── _typography-redesign   (Onest font, global type)
        ├── _bootstrap-overrides   (comprehensive BS4 overrides)
        ├── components/*           (layout + UI components)
        ├── pages/*                (page-specific styles)
        └── _dark-theme-redesign   (dark theme with new palette)

Compiled output: public/css/main.css
```

Webpack/Mix config (`webpack.mix.js`) is NOT modified — it already compiles `main.scss` to `public/css/main.css`.

---

## How It Works

### Override Strategy
1. **CSS Specificity**: The redesign files use the same selectors as the original styles, but load LATER in the cascade, so they win
2. **`!important`**: Used sparingly on key properties (background-color, border-radius) where the original styles also use `!important`
3. **Variable independence**: Redesign variables use `$r-` prefix to avoid conflicts with original `$variables`

### Variable Naming
All redesign variables are prefixed with `$r-` to distinguish them from original Anagami ERP variables:
- `$r-primary` vs `$blue`
- `$r-border-color` vs `$grey`
- `$r-text-primary` vs `$dark_text`

### Dark Theme
The redesign's `_dark-theme-redesign.scss` loads AFTER both the original `dark-theme.scss` AND after the redesign's light theme styles. This means:
- It overrides both the original dark theme AND the redesign's light styles
- All selectors are scoped under `body.dark-theme { ... }` (same as original)
- The dark theme toggle mechanism (user()->dark_theme) remains unchanged

---

## How to Add New Components

1. Create `resources/sass/redesign/components/_new-component.scss`
2. Use redesign variables (`$r-primary`, `$r-border-radius`, etc.)
3. Use redesign mixins where applicable (`@include card-style`, `@include hover-lift`, etc.)
4. Add `@import "components/new-component"` to `redesign.scss` (in the Components section)
5. Recompile: `npx mix`

### Template for new component:

```scss
// ============================================================================
// Anagami ERP Redesign — Component Name
// ============================================================================

.component-class {
  background-color: $r-card-bg;
  border: 1px solid $r-border-color;
  border-radius: $r-border-radius;
  padding: $r-card-padding;
  // ...
}
```

---

## How to Add Page-Specific Styles

1. Create `resources/sass/redesign/pages/_page-name.scss`
2. Add `@import "pages/page-name"` to `redesign.scss` (in the Pages section)
3. Recompile

---

## Reverting the Redesign

To completely revert:
1. Remove the import line from `resources/scss/main.scss`
2. Recompile: `npx mix`
3. Optionally delete `resources/sass/redesign/` and `docs/redesign/`

To temporarily disable:
1. Comment out the import in `main.scss`: `// @import "../sass/redesign/redesign";`
2. Recompile
