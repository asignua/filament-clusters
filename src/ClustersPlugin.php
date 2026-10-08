<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registering the plugin is optional: the stylesheet is a Filament asset and the {@see Cluster} component works on
 * any panel (and in a plain Livewire form) once the package is installed and `php artisan filament:assets` has run.
 * The plugin exists so a panel can list it, and as a place for future per-panel options.
 */
class ClustersPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'asignua-filament-clusters';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
