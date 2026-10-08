# Changelog

All notable changes to `asignua/filament-clusters` are documented here.

## 1.0.0 - Unreleased

- `Cluster` field group: one label, one bordered control, joined borders, shared focus ring, errors of all children under it.
- `columns()` (integer = every screen, or per breakpoint), `stackBelow()`, per-child column spans.
- Plain-CSS Filament asset with dark mode and RTL support; translations in 10 languages.
- Accessible: child `aria-describedby` points at the cluster's error and helper text; CSS lives in `@layer components` with RTL-aware dividers.
