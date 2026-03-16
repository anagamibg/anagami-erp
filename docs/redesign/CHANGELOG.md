# Frontend Redesign Changelog

All notable changes to the Anagami ERP frontend redesign are documented here.

---

## [2026-03-20] — Full Redesign Implementation (Phases 1–5)

### Phase 1: Foundations
#### Added
- `resources/sass/redesign/_variables-redesign.scss` — New design tokens: colors, spacing, shadows, borders, transitions, dark theme variables
- `resources/sass/redesign/_typography-redesign.scss` — Onest font import & global typography system (font sizes, line heights, heading styles)
- `resources/sass/redesign/_bootstrap-overrides.scss` — Comprehensive Bootstrap 4 component overrides (cards, buttons, forms, tables, modals, alerts, badges, pagination, tabs, dropdowns, progress bars, tooltips, scrollbars, SweetAlert2)
- `resources/sass/redesign/_mixins-redesign.scss` — Reusable mixins (truncate, card-style, flex helpers, status-dot, hover-lift, focus-ring, smooth-scroll)

### Phase 2: Layout Components
#### Added
- `resources/sass/redesign/components/_sidebar.scss` — Dark sidebar with soft active states, section labels, smooth scrollbar, toggled state, mobile support, theme class overrides
- `resources/sass/redesign/components/_header.scss` — White header with redesigned search, notification dropdown, icon styling, user menu
- `resources/sass/redesign/components/_content.scss` — Content area spacing, filter box, page title, body wrapper

### Phase 3: UI Components
#### Added
- `resources/sass/redesign/components/_cards.scss` — Card enhancements, stat/KPI card styles, hover effects
- `resources/sass/redesign/components/_buttons.scss` — Action buttons, button groups, active toggle, icon buttons
- `resources/sass/redesign/components/_tables.scss` — DataTables wrapper, status dot indicators, responsive overrides
- `resources/sass/redesign/components/_forms.scss` — Form group spacing, Dropify, Quill editor, Tagify, daterangepicker, datepicker overrides
- `resources/sass/redesign/components/_modals.scss` — Modal dialog, ajax form modals, task detail panel
- `resources/sass/redesign/components/_alerts.scss` — Alert layout with icon support
- `resources/sass/redesign/components/_badges.scss` — Status badges with dot indicators, pill-shaped labels
- `resources/sass/redesign/components/_dropdowns.scss` — Dashboard settings dropdown, filter dropdowns
- `resources/sass/redesign/components/_pagination.scss` — DataTables pagination styling
- `resources/sass/redesign/components/_tabs.scss` — Custom tabs, secondary tabs, settings sidebar tabs

### Phase 4: Page-Specific Styles
#### Added
- `resources/sass/redesign/pages/_dashboard.scss` — Dashboard KPI cards, chart containers, recent activity, FullCalendar overrides
- `resources/sass/redesign/pages/_login.scss` — Modern login/register page styling
- `resources/sass/redesign/pages/_invoices.scss` — Invoice detail, create invoice, status badges, finance page styles
- `resources/sass/redesign/pages/_projects.scss` — Project menu, task board, board columns, task search
- `resources/sass/redesign/pages/_settings.scss` — Settings layout, permission table, list groups

### Phase 5: Dark Theme
#### Added
- `resources/sass/redesign/_dark-theme-redesign.scss` — Complete dark theme using redesign palette (#0f172a bg, #1e293b cards, #334155 borders), covers all components, buttons retain primary color, sidebar/header dark variants, scrollbar styling

### Integration
#### Added
- `resources/sass/redesign/redesign.scss` — Main entry point importing all redesign partials in correct order

#### Modified
- `resources/scss/main.scss` — Added redesign import at end of file (after dark-theme, before EOF)

### Documentation
#### Added
- `docs/redesign/CHANGELOG.md` — This file
- `docs/redesign/DESIGN-SYSTEM.md` — Complete design system documentation
- `docs/redesign/MODIFIED-FILES.md` — All modified files list
- `docs/redesign/UPGRADE-GUIDE.md` — Anagami ERP upgrade instructions
- `docs/redesign/ARCHITECTURE.md` — Redesign layer architecture
