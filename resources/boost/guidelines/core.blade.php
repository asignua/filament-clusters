## Filament Clusters (asignua/filament-clusters)

- `Asignua\FilamentClusters\Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')` draws several fields as ONE input row. It has no state: children keep their own state paths and rules.
- `columns(4)` = 4 columns on every screen (not `lg` only); `columns(['default' => 1, 'sm' => 2])` or `stackBelow('md')` to stack on mobile. Child width: `->columnSpan(['default' => 3])`.
- Put hint/helper text/label on the cluster; the children's own are not drawn. Errors of all children appear once under the cluster.
- Run `php artisan filament:assets` after installing; registering `ClustersPlugin` is optional.
