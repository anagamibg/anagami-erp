# Anagami ERP Upgrade Guide — Preserving the Redesign

This guide explains how to preserve the Anagami ERP frontend redesign when upgrading to a newer version of Anagami ERP.

---

## Architecture Overview

The redesign uses an **override layer** approach:
- All redesign styles live in `resources/sass/redesign/` (new directory)
- Only ONE existing file was modified: `resources/scss/main.scss` (3 lines added at the end)
- No Blade templates, PHP files, or JavaScript files were changed
- The redesign CSS loads AFTER all original styles, overriding them via CSS specificity

---

## Files Safe to Overwrite (from Anagami ERP update)

These files can be safely replaced with the upstream version:

- `resources/scss/variables.scss`
- `resources/scss/sidebar.scss`
- `resources/scss/includes/header.scss`
- `resources/scss/includes/tabs.scss`
- `resources/scss/includes/reset.scss`
- `resources/scss/custom.scss`
- `resources/scss/style.scss`
- `resources/scss/form.scss`
- `resources/scss/btn.scss`
- `resources/scss/cards.scss`
- `resources/scss/tables.scss`
- `resources/scss/pagination.scss`
- `resources/scss/labels.scss`
- `resources/scss/modal.scss`
- `resources/scss/login.scss`
- `resources/scss/invoice.scss`
- `resources/scss/create-invoice.scss`
- `resources/scss/project.scss`
- `resources/scss/client.scss`
- `resources/scss/dashboard.scss`
- `resources/scss/settings.scss`
- `resources/scss/task_list.scss`
- `resources/scss/task-board.scss`
- `resources/scss/filter.scss`
- `resources/scss/messages.scss`
- `resources/scss/ticket.scss`
- `resources/scss/recruit.scss`
- `resources/scss/typography.scss`
- `resources/scss/ui_inventory.scss`
- `resources/scss/avatar.scss`
- `resources/scss/colors.scss`
- `resources/scss/rtl.scss`
- `resources/scss/dark-theme.scss`
- `resources/scss/custom_theme.scss`
- `webpack.mix.js`

**The redesign overrides all of these via CSS specificity, so upstream changes to these files will not break the redesign.**

---

## Files With Custom Changes (merge carefully)

### `resources/scss/main.scss`

The ONLY change is 3 lines added at the very end of the file:

```scss
// ============================================================================
// REDESIGN OVERRIDE LAYER (2026-03-20)
// Loads after all original styles to apply the new design system
// without modifying original SCSS files. See docs/redesign/ for details.
// ============================================================================
@import "../sass/redesign/redesign";
```

**Merge strategy**: After accepting the upstream `main.scss`, simply re-add these lines at the end.

---

## Upgrade Procedure

### Step 1: Accept upstream changes
Replace all Anagami ERP files with the new upstream version, including `main.scss`.

### Step 2: Re-add redesign import
Add the following to the END of `resources/scss/main.scss`:

```scss
@import "../sass/redesign/redesign";
```

### Step 3: Recompile
```bash
npx mix
```

### Step 4: Verify
- Check that the redesign styles are applied (dark sidebar, Onest font, rounded cards)
- Check dark theme toggle still works
- If any new upstream components look unstyled, add overrides in the appropriate `resources/sass/redesign/components/` file

### Step 5: Handle new components
If Anagami ERP adds new components in the update:
1. Check if they inherit Bootstrap classes (likely already styled by `_bootstrap-overrides.scss`)
2. If not, create a new partial in `resources/sass/redesign/components/` and import it in `redesign.scss`

---

## Troubleshooting

### Redesign not applied after upgrade
- Check that `@import "../sass/redesign/redesign"` is at the END of `main.scss`
- Run `npx mix` to recompile
- Clear browser cache

### Some components look broken
- The redesign uses `!important` on key properties to ensure overrides work
- If upstream adds new `!important` declarations, you may need to increase specificity in the redesign files
- Check `resources/sass/redesign/components/` for the relevant component file

### Dark theme issues
- The redesign includes its own `_dark-theme-redesign.scss` which overrides the original
- If upstream changes `dark-theme.scss`, the redesign's dark theme should still take precedence (loaded later)
- Test both light and dark modes after upgrade

---

## Important: Custom Modifications (CUS_000326-329)

The frontend redesign is independent of the custom PHP/Blade modifications (CUS_000326-329). Both must be preserved during upgrades:
- **Redesign**: Follow this guide (CSS/SCSS only)
- **Custom features**: See CLAUDE.md for the list of custom-modified PHP/Blade files
