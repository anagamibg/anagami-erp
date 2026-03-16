# Anagami ERP Design System

Reference: `demo.png` — Clean finance dashboard with spacious, professional SaaS aesthetic.

---

## Font

- **Family**: Onest (Google Fonts)
- **Import**: `@import url('https://fonts.googleapis.com/css2?family=Onest:wght@300;400;500;600;700&display=swap')`
- **Stack**: `'Onest', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`

### Size Scale

| Token | Size | Usage |
|-------|------|-------|
| `$r-text-xs` | 11px | Labels, badges, fine print |
| `$r-text-sm` | 12px | Body text, table cells, form labels |
| `$r-text-base` | 13px | Default body size |
| `$r-text-md` | 14px | Emphasized body, h6 |
| `$r-text-lg` | 16px | Section headers, h5 |
| `$r-text-xl` | 18px | Page sub-headers, h4 |
| `$r-text-2xl` | 20px | h3 |
| `$r-text-3xl` | 24px | h2 |
| `$r-text-4xl` | 30px | h1 |
| `$r-text-5xl` | 36px | Hero text |

### Weight Scale

| Weight | Value | Usage |
|--------|-------|-------|
| Light | 300 | Muted labels |
| Regular | 400 | Body text |
| Medium | 500 | Labels, buttons, nav items |
| Semi-bold | 600 | Headings, active states |
| Bold | 700 | KPI numbers, hero text |

---

## Color Palette

### Primary

| Token | Hex | Usage |
|-------|-----|-------|
| `$r-primary` | `#4F46E5` | Primary actions, links, active states |
| `$r-primary-light` | `#EEF2FF` | Primary backgrounds, hover states |
| `$r-primary-dark` | `#3730A3` | Pressed/active states |
| `$r-primary-hover` | `#4338CA` | Hover state |

### Sidebar

| Token | Hex | Usage |
|-------|-----|-------|
| `$r-sidebar-bg` | `#1a1d29` | Sidebar background |
| `$r-sidebar-text` | `#94a3b8` | Sidebar text |
| `$r-sidebar-active-bg` | `rgba(79,70,229,0.15)` | Active menu item |
| `$r-sidebar-active-text` | `#ffffff` | Active menu text |

### Backgrounds

| Token | Hex | Usage |
|-------|-----|-------|
| `$r-body-bg` | `#f8f9fb` | Page background |
| `$r-card-bg` | `#ffffff` | Card/panel backgrounds |
| `$r-header-bg` | `#ffffff` | Header background |

### Text

| Token | Hex | Usage |
|-------|-----|-------|
| `$r-text-primary` | `#1e293b` | Primary text |
| `$r-text-secondary` | `#64748b` | Secondary text, labels |
| `$r-text-muted` | `#94a3b8` | Muted text, placeholders |

### Status Colors

| Token | Hex | Light BG | Usage |
|-------|-----|----------|-------|
| `$r-success` | `#10b981` | `#ecfdf5` | Success, completed, paid |
| `$r-warning` | `#f59e0b` | `#fffbeb` | Warning, pending |
| `$r-danger` | `#ef4444` | `#fef2f2` | Error, overdue, cancelled |
| `$r-info` | `#3b82f6` | `#eff6ff` | Info, in progress |

### Borders

| Token | Hex |
|-------|-----|
| `$r-border-color` | `#e2e8f0` |

---

## Spacing System

Base unit: **4px**

| Token | Value | Usage |
|-------|-------|-------|
| `$r-space-1` | 4px | Minimal gaps |
| `$r-space-2` | 8px | Tight spacing |
| `$r-space-3` | 12px | Compact elements |
| `$r-space-4` | 16px | Standard gap |
| `$r-space-5` | 20px | Medium gap |
| `$r-space-6` | 24px | Card padding |
| `$r-space-8` | 32px | Section spacing |
| `$r-space-10` | 40px | Large sections |
| `$r-space-12` | 48px | Page sections |

### Component Spacing

| Purpose | Value |
|---------|-------|
| Card inner padding | 24px |
| Card gap | 20px |
| Section gap | 32px |

---

## Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `$r-border-radius-xs` | 6px | Small elements, tags |
| `$r-border-radius-sm` | 8px | Buttons, inputs, small cards |
| `$r-border-radius` | 12px | Cards, panels, tables |
| `$r-border-radius-lg` | 16px | Modals, large panels |
| `$r-border-radius-xl` | 20px | Hero sections |
| `$r-border-radius-pill` | 9999px | Badges, pills, switches |

---

## Shadows

| Token | Value | Usage |
|-------|-------|-------|
| `$r-shadow-xs` | `0 1px 2px rgba(0,0,0,0.03)` | Subtle |
| `$r-shadow-sm` | `0 1px 2px rgba(0,0,0,0.04)` | Cards at rest |
| `$r-shadow` | `0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)` | Cards on hover |
| `$r-shadow-md` | `0 4px 6px -1px rgba(0,0,0,0.06), ...` | Elevated cards |
| `$r-shadow-lg` | `0 10px 15px -3px rgba(0,0,0,0.06), ...` | Dropdowns, popovers |
| `$r-shadow-xl` | `0 20px 25px -5px rgba(0,0,0,0.08), ...` | Modals |

---

## Transitions

| Token | Value | Usage |
|-------|-------|-------|
| `$r-transition-fast` | `0.15s ease` | Hover states, toggles |
| `$r-transition` | `0.2s ease` | Standard animations |
| `$r-transition-slow` | `0.3s ease` | Layout shifts, sidebar |

---

## Dark Theme

Activated by `body.dark-theme` class.

| Token | Hex | Usage |
|-------|-----|-------|
| `$r-dark-bg` | `#0f172a` | Page background |
| `$r-dark-card` | `#1e293b` | Card backgrounds |
| `$r-dark-border` | `#334155` | Borders |
| `$r-dark-text` | `#e2e8f0` | Primary text |
| `$r-dark-text-muted` | `#94a3b8` | Muted text |
| `$r-dark-sidebar-bg` | `#0c0f1a` | Sidebar background |
| `$r-dark-header-bg` | `#1e293b` | Header background |

---

## Component Specifications

### Sidebar
- Width: 260px (collapsed: 68px)
- Background: `#1a1d29` (always dark, regardless of theme)
- Nav item padding: 9px 20px
- Active state: `rgba(79,70,229,0.15)` background with white text
- Icon size: 15px, 20px width, 12px margin-right

### Header
- Height: 64px
- Background: white with 1px bottom border
- Icon buttons: 36x36px, 8px border-radius

### Cards
- Padding: 24px
- Border-radius: 12px
- Shadow: `$r-shadow-sm` (rest) → `$r-shadow` (hover)
- Border: 1px solid `$r-border-color`

### Buttons
- Border-radius: 8px
- Padding: 8px 16px (default), 5px 12px (sm), 12px 24px (lg)
- Font-weight: 500
- Primary: Indigo `#4F46E5`

### Tables
- Header: uppercase, 11px, letter-spacing 0.05em, `$r-body-bg` background
- Cell padding: 12px 16px
- Row hover: `rgba(primary, 0.02)` background

### Forms
- Input border-radius: 8px
- Input padding: 8px 12px
- Focus: Primary color border + 3px focus ring

### Badges
- Pill-shaped (border-radius: 9999px)
- Padding: 4px 10px
- Status dots: 6-8px circles
