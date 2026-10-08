<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * Groups several fields into ONE visual input row: a shared label, one bordered control with joined borders and a
 * shared focus ring. It owns no state: every child keeps its own state path and validation rules, the cluster only
 * draws them together and gathers their error messages under the control.
 */
class Cluster extends Field implements HasEmbeddedView
{
    public const string CELL_VIEW = 'filament-clusters::cell';

    public const string WRAPPER_VIEW = 'filament-clusters::cluster-wrapper';

    /** Breakpoints in ascending order, as Filament's `columns()` knows them. */
    private const array BREAKPOINTS = ['default', 'sm', 'md', 'lg', 'xl', '2xl'];

    protected ?string $stackBelow = null;

    /**
     * @param array<Component>|Closure|string|null $schema the child components; a string is the cluster's
     *                                                     internal name (rarely needed)
     */
    public static function make(string|array|Closure|null $schema = null): static
    {
        $name = is_string($schema) ? $schema : static::getDefaultName();

        $static = parent::make($name);

        if (!is_string($schema) && $schema !== null) {
            $static->schema($schema);
        }

        return $static;
    }

    public static function getDefaultName(): ?string
    {
        return 'cluster';
    }

    protected function setUp(): void
    {
        parent::setUp();

        // The cluster is a layout, not a value: no state path of its own, so the children keep the paths they
        // would have had outside it (`data.first`, not `data.cluster.first`).
        $this->statePath(null);

        $this->fieldWrapperView(self::WRAPPER_VIEW);
    }

    /**
     * Number of columns. A plain integer means "this many on every screen" (a single row), unlike Filament's
     * layout components where it only applies from `lg`. Use an array for breakpoints, or {@see stackBelow()}.
     *
     * @param array<string, Closure|int|null>|Closure|int|null $columns
     */
    public function columns(array|int|Closure|null $columns = 2): static
    {
        if ($columns === null) {
            // "auto": one column per visible child, which is what a cluster without `columns()` does.
            $this->columns = null;

            return $this;
        }

        // An integer (plain or returned by a closure) means "every screen"; Filament would map it to `lg` only.
        if (is_int($columns) || $columns instanceof Closure) {
            $columns = ['default' => $columns];
        }

        return parent::columns($columns);
    }

    /**
     * Stack the children vertically (one per line) below the given breakpoint, side by side from it up.
     */
    public function stackBelow(?string $breakpoint = 'sm'): static
    {
        $this->stackBelow = $breakpoint;

        return $this;
    }

    /**
     * @return array<string, int|null>
     */
    public function getAllColumns(): array
    {
        if ($this->columns === null) {
            $columns = ['default' => max(1, $this->countVisibleChildren())];
        } else {
            $columns = parent::getAllColumns();
        }

        if ($this->stackBelow !== null && $this->stackBelow !== 'default' && ($columns['default'] ?? 1) > 1) {
            $columns[$this->stackBelow] ??= $columns['default'];
            $columns['default'] = 1;
        }

        return $columns;
    }

    /**
     * A cluster has no state path to name it by, so the id comes from the first child's path (`form.first_name` ->
     * `form.first_name-cluster`): stable across requests, which Livewire's DOM diffing and the label's
     * `aria-labelledby` need, and unique because a path can belong to one field only.
     */
    public function getId(): ?string
    {
        $id = parent::getId();

        if (filled($id)) {
            return $id;
        }

        foreach ($this->getChildFields() as $field) {
            if (filled($path = $field->getStatePath())) {
                return $path.'-cluster';
            }
        }

        return null;
    }

    /**
     * No label unless one is set: Filament would otherwise derive "Cluster" from the internal name.
     */
    public function getDefaultLabel(): string
    {
        return '';
    }

    public function isMarkedAsRequired(): bool
    {
        $explicit = $this->evaluate($this->isMarkedAsRequired);

        if ($explicit !== null) {
            return (bool) $explicit;
        }

        // `->required()` on the cluster adds no rule (the children own those) but forces the marker.
        if ($this->isRequired()) {
            return true;
        }

        foreach ($this->getChildFields() as $field) {
            if ($field->isMarkedAsRequired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Visual only (the dimmed frame). Not an override of `isDisabled()`: a child schema asks its parent component
     * whether it is disabled, so deriving that from the children would recurse.
     */
    public function areChildrenDisabled(): bool
    {
        $fields = $this->getChildFields();

        if ($fields === []) {
            return false;
        }

        foreach ($fields as $field) {
            if (!$field->isDisabled()) {
                return false;
            }
        }

        return true;
    }

    public function hasValidationError(): bool
    {
        return $this->getChildErrorMessages() !== [];
    }

    /**
     * Distinct validation messages of every child (and of their nested paths), in child order.
     *
     * @return array<int, string>
     */
    public function getChildErrorMessages(): array
    {
        $errors = view()->shared('errors');

        if (!$errors instanceof ViewErrorBag) {
            return [];
        }

        $bag = $errors->getBag('default');

        if (!$bag instanceof MessageBag || $bag->isEmpty()) {
            return [];
        }

        $messages = [];

        foreach ($this->getChildFields() as $field) {
            $path = $field->getStatePath();

            if (blank($path)) {
                continue;
            }

            $all = [...Arr::wrap($bag->get($path)), ...Arr::flatten($bag->get("{$path}.*"))];

            // Like stock Filament: the first message per field, unless the child asks for all of them.
            if (!$field->shouldShowAllValidationMessages()) {
                $all = array_slice($all, 0, 1);
            }

            foreach ($all as $message) {
                $messages[] = (string) $message;
            }
        }

        return array_values(array_unique($messages));
    }

    public function toEmbeddedHtml(): string
    {
        $attributes = $this->getExtraAttributeBag()
            ->merge([
                'id' => $this->getId(),
                'role' => 'group',
                'aria-labelledby' => filled($this->getId()) ? $this->getId().'-label' : null,
                'aria-invalid' => $this->hasValidationError() ? 'true' : null,
            ], escape: false)
            ->merge($this->getModeAttributes(), escape: false)
            ->class([
                'fi-cl',
                'fi-cl-disabled' => $this->isDisabled() || $this->areChildrenDisabled(),
                'fi-cl-invalid' => $this->hasValidationError(),
            ]);

        return $this->wrapEmbeddedHtml(
            '<div '.$attributes->toHtml().'>'.($this->getChildSchema()?->toHtml() ?? '').'</div>',
            labelTag: 'span',
        );
    }

    protected function configureChildSchema(Schema $schema, string $key): Schema
    {
        $schema = parent::configureChildSchema($schema, $key);

        if ($key === 'default') {
            $schema
                ->gap(false)
                ->fieldWrapperView(self::CELL_VIEW);
        }

        return $schema;
    }

    /**
     * Points the child control's `aria-describedby` at elements that exist: the cluster draws one error block and
     * one helper text for everybody, so the child's own `-error` / helper ids are swapped for the cluster's.
     */
    public function describeChild(Field $child, string $html): string
    {
        $id = $this->getId();
        $childId = (string) $child->getId();

        if (blank($id) || blank($childId)) {
            return $html;
        }

        $own = [$childId.'-error', $childId.'-helper-text'];
        $extra = [];

        if (filled($this->getHelperTextId())) {
            $extra[] = $this->getHelperTextId();
        }

        // Only the child that failed points at the cluster's error block.
        if ($child->hasValidationError()) {
            $extra[] = $id.'-error';
        }

        $merge = static function (string $current) use ($own, $extra): string {
            $tokens = array_filter(
                preg_split('/\s+/', trim($current)) ?: [],
                static fn (string $token): bool => $token !== '' && !in_array($token, $own, true),
            );

            return implode(' ', array_values(array_unique([...$tokens, ...$extra])));
        };

        $result = preg_replace_callback(
            '/<(?:input|select|textarea|button)\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>/i',
            static function (array $match) use ($childId, $merge, $extra): string {
                $tag = $match[0];

                if (!str_contains($tag, ' id="'.$childId.'"')) {
                    return $tag;
                }

                if (preg_match('/\saria-describedby="([^"]*)"/', $tag, $existing) === 1) {
                    return str_replace($existing[0], ' aria-describedby="'.$merge(html_entity_decode($existing[1])).'"', $tag);
                }

                if ($extra === []) {
                    return $tag;
                }

                return substr($tag, 0, -1).' aria-describedby="'.implode(' ', $extra).'">';
            },
            $html,
        );

        return $result ?? $html;
    }

    /**
     * @return array<int, Field>
     */
    protected function getChildFields(): array
    {
        $schema = $this->getChildSchema();

        return $schema === null ? [] : array_values($schema->getFlatFields());
    }

    private function countVisibleChildren(): int
    {
        $schema = $this->getChildSchema();

        return $schema === null ? 0 : count($schema->getComponents());
    }

    /**
     * Tells the stylesheet, per breakpoint, how the children are laid out (`row`, `col` or `grid`), so it can draw
     * the dividers on the right side. Only breakpoints the columns set explicitly are emitted; the others inherit.
     *
     * @return array<string, string>
     */
    private function getModeAttributes(): array
    {
        $columns = $this->getAllColumns();
        $count = $this->countVisibleChildren();
        $attributes = [];

        foreach (self::BREAKPOINTS as $breakpoint) {
            $value = $columns[$breakpoint] ?? null;

            if ($value === null) {
                continue;
            }

            $attributes["data-cl-{$breakpoint}"] = match (true) {
                $value <= 1 => 'col',
                $value >= $count => 'row',
                default => 'grid',
            };
        }

        return $attributes;
    }
}
