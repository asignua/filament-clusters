<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters\Tests\Feature;

use Asignua\FilamentClusters\ClustersPlugin;
use Asignua\FilamentClusters\ClustersServiceProvider;
use Asignua\FilamentClusters\Tests\TestCase;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentAsset;

class SmokeTest extends TestCase
{
    public function test_the_panel_boots(): void
    {
        $this->assertSame('admin', Filament::getCurrentPanel()?->getId());
    }

    public function test_the_plugin_is_registered_on_the_panel(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertTrue($panel->hasPlugin('asignua-filament-clusters'));
        $this->assertInstanceOf(ClustersPlugin::class, $panel->getPlugin('asignua-filament-clusters'));
    }

    public function test_the_translations_are_loaded(): void
    {
        $this->assertSame(
            'Name: First',
            __('filament-clusters::filament-clusters.child_label', ['cluster' => 'Name', 'field' => 'First']),
        );
    }

    public function test_the_stylesheet_is_a_registered_filament_asset(): void
    {
        $href = FilamentAsset::getStyleHref(ClustersServiceProvider::STYLESHEET, ClustersServiceProvider::PACKAGE);

        $this->assertStringContainsString('filament-clusters', $href);
    }

    public function test_the_shipped_stylesheet_is_a_verbatim_copy_of_the_source(): void
    {
        $this->assertFileEquals(
            __DIR__.'/../../resources/css/clusters.css',
            __DIR__.'/../../resources/dist/clusters.css',
        );
    }
}
