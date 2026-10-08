# Filament Clusters

[![Tests](https://img.shields.io/github/actions/workflow/status/asignua/filament-clusters/tests.yml?branch=main&label=tests)](https://github.com/asignua/filament-clusters/actions/workflows/tests.yml)

Put several fields into **one input row** in Filament 5: one label, one bordered control, joined borders and a shared focus ring.
Address lines, name parts, ranges, money plus currency. Every child keeps its own state path and validation; the cluster only
draws them together and gathers their errors. A maintained Filament 5 successor of `guava/filament-clusters`.

## Screenshots

TODO: add images to `art/` (cover.jpg first) and reference them here.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- Filament 5

## Installation

```bash
composer require asignua/filament-clusters
php artisan filament:assets
```

Registering the plugin is optional (it only lists the plugin on the panel):

```php
use Asignua\FilamentClusters\ClustersPlugin;

$panel->plugin(ClustersPlugin::make());
```

Migrating from Guava: change the import to `Asignua\FilamentClusters\Cluster`. Note that an integer `columns()` now means "on every screen".

## Usage

```php
use Asignua\FilamentClusters\Cluster;
use Filament\Forms\Components\TextInput;

Cluster::make([
    TextInput::make('first_name')->label('First name')->required(),
    TextInput::make('last_name')->label('Last name')->required(),
])
    ->label('Name')
    ->hint('Legal name')
    ->helperText('As in the passport');
```

The children are saved as `first_name` / `last_name`, exactly as without the cluster.

### Columns and stacking

```php
Cluster::make([...])->columns(4);                              // 4 equal columns on every screen
Cluster::make([...])->columns(['default' => 1, 'sm' => 2]);    // stacked on mobile
Cluster::make([...])->stackBelow('md');                        // one per line below md, a row from md up
Cluster::make([...])->columns(1);                              // a vertical cluster
```

Per-child width: `->columnSpan(['default' => 3])` (a bare `columnSpan(3)` is Filament's `lg`-only span).

### Recipes

```php
// Money + currency
Cluster::make([
    Select::make('currency')->options(['EUR' => 'EUR', 'USD' => 'USD'])->native(false)->default('EUR'),
    TextInput::make('amount')->numeric()->minValue(0)->columnSpan(['default' => 3]),
])->label('Price')->columns(4);

// Range
Cluster::make([
    TextInput::make('min')->numeric()->prefix('from'),
    TextInput::make('max')->numeric()->prefix('to')->gte('min'),
])->label('Quantity');

// Address lines
Cluster::make([
    TextInput::make('street')->columnSpan(['default' => 1, 'sm' => 3]),
    TextInput::make('house')->columnSpan(['default' => 1, 'sm' => 1]),
    TextInput::make('zip'),
    TextInput::make('city')->columnSpan(['default' => 1, 'sm' => 3]),
])->label('Address')->columns(['default' => 1, 'sm' => 4]);

// Date + time
Cluster::make([DatePicker::make('day'), TimePicker::make('time')])->label('When')->stackBelow('sm');
```

## Configuration

There is no config file. Restyle through CSS variables on `.fi-cl`: `--fi-cl-radius`, `--fi-cl-divider`, `--fi-cl-ring`,
`--fi-cl-ring-focus`, `--fi-cl-ring-invalid`. Dark mode follows Filament's `.dark` class.

## Gotchas

- **Children's own labels, hints and helper text are not drawn** (the cluster supplies them). Their labels stay as screen-reader names.
- **Multi-row clusters** (more children than columns) draw a hairline on both sides of every later cell, so the first cell of a second row shows a short line at the outer edge.
- **Don't override `isDisabled()`-style logic from the children**: the dimmed frame is shown when every child is disabled, the cluster itself is not disabled by it.
- **Only fields with a single-line frame** (`fi-input-wrp`) look right inside; textareas, toggles and editors do not.
- Outside a panel (plain Livewire page) load the stylesheet with `FilamentAsset::getStyleHref('filament-clusters', 'asignua/filament-clusters')`.

## Translations

English, Ukrainian, German, Spanish, French, Italian, Dutch, Polish, Brazilian Portuguese and Turkish under
`filament-clusters::filament-clusters` (the screen-reader name of a child). Publish with `--tag=filament-clusters-translations`.

## AI agents

Laravel Boost guidelines ship in `resources/boost/guidelines/core.blade.php`.

## Testing

```bash
composer install
composer test
composer analyse
vendor/bin/pint --test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
