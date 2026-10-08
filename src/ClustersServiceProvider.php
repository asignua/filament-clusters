<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ClustersServiceProvider extends PackageServiceProvider
{
    public const string PACKAGE = 'asignua/filament-clusters';

    public const string STYLESHEET = 'filament-clusters';

    public static string $name = 'filament-clusters';

    public function configurePackage(Package $package): void
    {
        // Translations live in resources/lang/<locale>/filament-clusters.php and are read as
        // `__('filament-clusters::filament-clusters.<key>')`. Publish tag: `filament-clusters-translations`.
        $package->name(static::$name)
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make(self::STYLESHEET, __DIR__.'/../resources/dist/clusters.css'),
        ], self::PACKAGE);
    }
}
