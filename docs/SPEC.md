# filament-clusters: spec

## Scope
A free (MIT) Filament 5 successor of `guava/filament-clusters` (archived, Filament 3 only). One component, `Cluster`, groups
several fields into ONE visual input row: one shared label, the fields side by side inside a single bordered control (joined
borders, shared focus ring), each child keeping its own state path and validation.

## Public API
- `Asignua\FilamentClusters\Cluster::make(array|Closure $schema)` (extends `Filament\Forms\Components\Field`, owns no state).
- Inherited from `Field`: `label()`, `hiddenLabel()`, `hint()`, `hintIcon()`, `helperText()`, `extraAttributes()`, `markAsRequired()`, `hidden()`/`visible()`, `columnSpan()`.
- `columns(int|array)`: an integer is "this many on every screen" (unlike Filament layouts, where it means `lg`); arrays use Filament breakpoints. Default: one column per visible child.
- `stackBelow('sm'|'md'|...)`: one child per line below the breakpoint, side by side from it up.
- Children: any field that draws a `fi-input-wrp` (TextInput, Select, DatePicker, TimePicker, ColorPicker, affixes...). Per-child width: `->columnSpan(['default' => 3])`.
- Required marker: shown when any child is required, or forced with `markAsRequired(bool)`.
- Errors: the messages of all children are listed once under the cluster; the frame turns red; only the invalid child's input keeps `fi-invalid`.
- Accessibility: `role="group"` + `aria-labelledby`; every child gets a screen-reader label "Cluster: Child" (translatable).

## Extension points
- Stylesheet `resources/dist/clusters.css` is a Filament asset, plain CSS scoped under `.fi-cl`, customisable through CSS variables (`--fi-cl-radius`, `--fi-cl-divider`, ...).
- Views `filament-clusters::cell` (child wrapper) and `filament-clusters::cluster-wrapper` (label/error wrapper) are publishable.
- `ClustersPlugin` is optional; the component works on any panel.

## Non-goals
- No cluster-level validation rules or state (it is a layout, not a value).
- Children's own hint/helper text/label are not drawn (put them on the cluster).
- Not for checkboxes/toggles/textareas/rich editors (they do not draw a single-line input frame).
- No Tailwind build step.
