# Changelog

All notable changes to `asignua/filament-clusters` are documented here.

## 1.0.1 - 2026-10-12

- Fix: in a multi-row cluster (`columns(2)` with four fields, spans included) the cells on the first row no longer get a hairline along their top edge and the cells starting a row none on their left.
- Docs: demo fields have explicit labels, so a server-side rule message reads «The end date field must be a date after or equal to start date.»
- Docs: screenshots, cover and a workbench demo.

## 1.0.0 - 2026-10-09

- Fix: `Cluster::make([...])` without a name no longer gets the fixed name `cluster`; the name is generated from the child field names (stable across requests), so several unnamed clusters in one schema do not collide. Pass a string to `make()` to choose your own.
- `columns(int)` is documented and tested as `['default' => n]` (every screen, not Filament's `lg`-only), including `columns(fn () => n)`.

- `Cluster` field group: one label, one bordered control, joined borders, shared focus ring, errors of all children under it.
- `columns()` (integer = every screen, or per breakpoint), `stackBelow()`, per-child column spans.
- Plain-CSS Filament asset with dark mode and RTL support; translations in 10 languages.
- Accessible: child `aria-describedby` points at the cluster's error and helper text; CSS lives in `@layer components` with RTL-aware dividers.
