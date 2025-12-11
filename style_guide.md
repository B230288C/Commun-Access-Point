# Style Guide

## 1. Typography
- Primary Font: Inter
- Font Sizes:
  - Page Title: 24px, Bold
  - Section Title: 18px, Semi-Bold
  - Body Text: 14px, Regular
  - Description/Helper Text: 12px, Regular
- Line Height: 1.5

## 2. Color State Rules

All interactive components (buttons, icons, toggles, links, inputs, table rows, etc.)
must follow these standardized state transformations.

### 2.1 Default
- Use the base color.

### 2.2 Hover
- Lightness: -10%
- Hue & Saturation unchanged.

### 2.3 Active
- Lightness: -15%
- Darker than hover.

### 2.4 Disabled
- Opacity: 50%
- Saturation: -50%
- No hover or active interaction allowed.

### 2.5 Muted Variant
- Saturation: -40%
- Lightness: +10%
- Used for low-emphasis UI elements (background, labels, secondary UI parts).


## 3. Color Palette
### Primary Colors
- Primary: #2563EB
- Primary Hover: #1E4FCC
- Primary Active: #1B49B2
- Primary Disabled: #557ed688
- Primary Muted: #6F93ED

### Neutral Colors
- Text Primary: #1F1F1F
- Text Secondary: #6D6D6D
- Border: #E0E0E0
- Background: #FAFAFA

## 4. Buttons
- Height: 40px
- Width: 77px
- Radius: 8px
- Padding: 12px
- Font: 14px, Medium

### Button States
- Default: Primary
- Hover: Primary Hover
- Active: Primary Active
- Disabled: Primary Disabled

## 5. Icons
- Library: Font Awesome
- Size: 20px
- Icon States: default / hover / active

## 6. Cards
- Card Background: #FFFFFF
- Corner Radius: 12px
- Stroke width: 1px
- Stroke color: #000000ff

## 7. Components
- Corner Radius: 8px

### Frame (Calendar Event - Left 1/5)
- Width: 20% of calendar cell (left side)
- Height: Matches event duration
- Border Radius: 4px left corners only
- Active State:
  - Background: #2563EB (Primary)
  - Hover: #1E4FCC
  - Active: #1B49B2
- Inactive State:
  - Background: #6D6D6D
  - Hover: #5A5A5A
  - Active: #4A4A4A
- Title: White, 12px, vertical text (rotated 180deg)

### Slot (Calendar Event - Right 4/5)
- Width: 80% of calendar cell (right side)
- Height: Matches slot duration
- Border Radius: 4px right corners only
- Border Left: 1px solid #E0E0E0
- Available State:
  - Background: #FFEBB7
  - Hover: #FFE49A
  - Active: #FFD970
- Booked State:
  - Background: #D1FAE5
  - Hover: #A7F3D0
  - Active: #6EE7B7
- Blocked State:
  - Background: #FEE2E2
  - Hover: #FECACA
  - Active: #FCA5A5
- Time Text: #1F1F1F, 12px

## 8. Spacing Scale
Use a consistent spacing scale for padding, margin, and gap:
- 4px  → XS
- 8px  → S
- 12px → M
- 16px → L
- 20px → XL
- 24px → 2XL

## 9. Elevation
- Level 0: No shadow (default UI)
- Level 1: Small shadow for cards
  - rgba(0,0,0,0.05) 0px 1px 2px
- Level 2: Medium shadow for floating panels
  - rgba(0,0,0,0.08) 0px 2px 10px
- Level 3: Large shadow for dialogs
  - rgba(0,0,0,0.12) 0px 4px 20px

## 10. Inputs
- Height: 40px
- Radius: 8px
- Border: 1px solid #E0E0E0
- Padding: 8px 12px
- Font: 14px Regular

### Input States
- Default Border: #E0E0E0
- Hover Border: darken(#E0E0E0, 10%)
- Focus Border: #2563EB
- Disabled: opacity 50%
- Error Border: #FF0000

## 11. Table
- Header Background: #F5F5F5
- Header Text: #6D6D6D
- Cell Padding: 12px
- Row Height: 48px
- Row Hover: lightness -10%
- Row Active: lightness -15%
- Border Color: #E0E0E0

## 12. Motion
- Transition Duration: 150ms
- Easing: ease-in-out
- Apply transitions to:
  - color
  - background-color
  - border-color
  - opacity
  - shadow

## 13. Naming Rules
Use consistent naming across components, files, and classes.

### CSS class naming:
- kebab-case
Example: appointment-card, slot-item

### Component naming:
- PascalCase
Example: AppointmentCard, CalendarSlot, CreateAppointmentModal
