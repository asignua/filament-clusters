@php
    use Asignua\FilamentClusters\Cluster;

    /** @var Cluster $field */
    $messages = $field->getChildErrorMessages();
@endphp

{{-- The stock wrapper does the label, hint, helper text and error markup; the errors come from the children. --}}
<x-filament-forms::field-wrapper
    :field="$field"
    :label-tag="$labelTag"
    :label-prefix="$labelPrefix"
    :label-suffix="$labelSuffix"
    :inline-label-vertical-alignment="$inlineLabelVerticalAlignment"
    :has-errors="$messages !== []"
    :error-message="count($messages) === 1 ? $messages[0] : null"
    :error-messages="count($messages) > 1 ? $messages : null"
    :attributes="$attributes"
>
    {{ $slot }}
</x-filament-forms::field-wrapper>
