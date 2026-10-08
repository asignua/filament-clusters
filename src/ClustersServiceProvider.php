<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ClustersServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-clusters';

    public function configurePackage(Package $package): void
    {
        // Translations live in resources/lang/<locale>/filament-clusters.php and are read as
        // `__('filament-clusters::filament-clusters.<key>')`. Publish tag: `filament-clusters-translations`.
        $package->name(static::$name)
            ->hasTranslations()
            ->hasViews();

        // Add a config file only when the plugin really has options: create config/filament-clusters.php and
        // chain `->hasConfigFile()` here (publish tag `filament-clusters-config`). Prefer fluent setters on the Plugin.
    }
}
