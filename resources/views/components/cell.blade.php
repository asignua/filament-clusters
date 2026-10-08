@php
    use Asignua\FilamentClusters\Cluster;
    use Filament\Forms\Components\Field;

    /** @var Field $field */
    $cluster = $field->getContainer()->getParentComponent();
    $clusterLabel = ($cluster instanceof Cluster) ? $cluster->getLabel() : null;
    $childLabel = ($field->isLabelHidden() || blank($field->getLabel())) ? null : $field->getLabel();
    $accessibleLabel = (filled($clusterLabel) && filled($childLabel))
        ? __('filament-clusters::filament-clusters.child_label', ['cluster' => $clusterLabel, 'field' => $childLabel])
        : ($childLabel ?: $clusterLabel);
@endphp

<div data-field-wrapper {{ $attributes->class(['fi-cl-cell']) }}>
    @if (filled($accessibleLabel))
        <label for="{{ $field->getId() }}" class="fi-sr-only">{{ $accessibleLabel }}</label>
    @endif

    {{ $slot }}
</div>
