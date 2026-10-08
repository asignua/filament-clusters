<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters\Tests\Feature;

use Asignua\FilamentClusters\Cluster;
use Asignua\FilamentClusters\Tests\TestCase;
use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Workbench\App\Livewire\SingleClusterForm;

/**
 * Independent review (2026-10-08). Tests whose name starts with `test_bug_` document a defect found in review and are
 * EXPECTED TO FAIL until the production code is fixed; everything else pins behaviour that already works.
 */
class IndependentReviewTest extends TestCase
{
    /**
     * @param array<int, mixed> $components
     */
    private function form(array $components): Testable
    {
        SingleClusterForm::$components = $components;

        return Livewire::test(SingleClusterForm::class);
    }

    /**
     * @param array<int, mixed> $components
     */
    private function render(array $components): string
    {
        return $this->form($components)->html();
    }

    /**
     * @return array<int, string>
     */
    private function ids(string $html): array
    {
        preg_match_all('/\sid="([^"]+)"/', $html, $matches);

        return $matches[1];
    }

    // ---------------------------------------------------------------------------------------------------------------
    // Accessibility
    // ---------------------------------------------------------------------------------------------------------------

    public function test_bug_aria_describedby_of_an_invalid_child_points_at_an_existing_element(): void
    {
        $html = $this->form([
            Cluster::make([TextInput::make('first')->required(), TextInput::make('last')])->label('Name'),
        ])->call('save')->html();

        $this->assertSame(1, preg_match_all('/aria-describedby="([^"]+)"/', $html, $matches));

        $ids = $this->ids($html);

        foreach (explode(' ', $matches[1][0]) as $describedBy) {
            $this->assertContains(
                $describedBy,
                $ids,
                "Child input has aria-describedby=\"{$describedBy}\" but no element carries that id: the error is drawn under the cluster as `<cluster-id>-error`.",
            );
        }
    }

    public function test_bug_aria_describedby_of_children_includes_the_cluster_helper_text(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->helperText('Some help'),
        ]);

        // The helper text is drawn once for the cluster; a screen reader focused on a child never hears it.
        $this->assertStringContainsString('id="data.a-cluster-helper-text"', $html);
        $this->assertMatchesRegularExpression('/aria-describedby="[^"]*data\.a-cluster-helper-text/', $html);
    }

    public function test_bug_a_hidden_label_still_names_the_group(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->hiddenLabel(),
        ]);

        // The sr-only <span id="…-label"> is rendered, but the group no longer references it.
        $this->assertMatchesRegularExpression('/role="group"[^>]*aria-label(ledby)?="/', $html);
    }

    // ---------------------------------------------------------------------------------------------------------------
    // Label defaults
    // ---------------------------------------------------------------------------------------------------------------

    public function test_bug_a_cluster_without_a_label_does_not_show_the_internal_name(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('first')->label('First'), TextInput::make('last')->label('Last')]),
        ]);

        // The label falls back to the internal name `cluster` -> an English "Cluster" heading and "Cluster: First"
        // screen-reader names, untranslatable.
        $this->assertDoesNotMatchRegularExpression('/fi-fo-field-label-content">\s*Cluster/', $html);
        $this->assertStringNotContainsString('Cluster: First', $html);
    }

    // ---------------------------------------------------------------------------------------------------------------
    // Columns
    // ---------------------------------------------------------------------------------------------------------------

    public function test_bug_a_closure_returning_an_integer_means_every_screen_like_a_plain_integer(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->columns(fn (): int => 4),
        ]);

        // Filament turns an int from a closure into `lg` only, so on mobile the cluster silently stacks.
        $this->assertStringContainsString('--cols-default: repeat(4, minmax(0, 1fr))', $html);
    }

    public function test_columns_can_be_set_twice_and_the_later_breakpoint_wins(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])
                ->label('Pair')
                ->columns(['default' => 1, 'md' => 2])
                ->columns(3),
        ]);

        $this->assertStringContainsString('--cols-default: repeat(3, minmax(0, 1fr))', $html);
        $this->assertStringContainsString('--cols-md: repeat(2, minmax(0, 1fr))', $html);
    }

    public function test_a_conditionally_hidden_child_does_not_count_as_a_column(): void
    {
        $html = $this->render([
            Cluster::make([
                TextInput::make('a'),
                TextInput::make('b')->hidden(fn (Get $get): bool => blank($get('a'))),
                TextInput::make('c'),
            ])->label('Three'),
        ]);

        $this->assertStringContainsString('--cols-default: repeat(2, minmax(0, 1fr))', $html);
    }

    // ---------------------------------------------------------------------------------------------------------------
    // State, validation, visibility
    // ---------------------------------------------------------------------------------------------------------------

    public function test_required_on_the_cluster_itself_adds_no_rule_and_no_state(): void
    {
        $this->form([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->required(),
        ])
            ->fillForm(['a' => null, 'b' => null])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('saved', ['a' => null, 'b' => null]);
    }

    public function test_bug_required_on_the_cluster_draws_the_marker(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->required(),
        ]);

        $this->assertStringContainsString('fi-fo-field-label-required-mark', $html);
    }

    public function test_a_hidden_required_child_does_not_draw_the_marker(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')->required()->hidden()])->label('Pair'),
        ]);

        $this->assertStringNotContainsString('fi-fo-field-label-required-mark', $html);
    }

    public function test_a_hidden_cluster_hides_and_skips_every_child(): void
    {
        $component = $this->form([
            Cluster::make([TextInput::make('a')->required(), TextInput::make('b')->required()])->label('Pair')->hidden(),
            TextInput::make('other'),
        ]);

        $this->assertStringNotContainsString('role="group"', $component->html());

        $component->fillForm(['other' => 'x'])->call('save')->assertHasNoFormErrors()->assertSet('saved', ['other' => 'x']);
    }

    public function test_a_disabled_cluster_disables_its_children_and_does_not_save_them(): void
    {
        $component = $this->form([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->disabled(),
        ]);

        $html = $component->html();

        $this->assertSame(2, preg_match_all('/<input[^>]*\sdisabled/', $html));
        $this->assertStringContainsString('fi-cl-disabled', $html);

        $component->set('data.a', 'tampered')->call('save')->assertSet('saved', []);
    }

    public function test_a_live_child_still_triggers_its_after_state_updated_hook(): void
    {
        $this->form([
            Cluster::make([
                TextInput::make('first')->live()->afterStateUpdated(fn (Set $set, ?string $state) => $set('last', strtoupper((string) $state))),
                TextInput::make('last'),
            ])->label('Name'),
        ])
            ->set('data.first', 'ada')
            ->assertSet('data.last', 'ADA');
    }

    public function test_two_clusters_get_distinct_ids(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('One'),
            Cluster::make([TextInput::make('c'), TextInput::make('d')])->label('Two'),
        ]);

        $ids = array_filter($this->ids($html), fn (string $id): bool => str_ends_with($id, '-cluster'));

        $this->assertCount(2, $ids);
        $this->assertCount(2, array_unique($ids));
    }

    public function test_a_cluster_with_no_visible_children_renders_without_errors(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a')->hidden()])->label('Empty'),
        ]);

        $this->assertStringContainsString('role="group"', $html);
    }

    public function test_a_cluster_works_inside_a_repeater(): void
    {
        $component = $this->form([
            Repeater::make('lines')
                ->schema([
                    Cluster::make([TextInput::make('street')->required(), TextInput::make('zip')])->label('Address'),
                ])
                ->defaultItems(1),
        ]);

        $html = $component->html();

        $this->assertMatchesRegularExpression('/wire:model="data\.lines\.[^".]+\.street"/', $html);
        $this->assertStringNotContainsString('.cluster.', $html);

        $component->call('save');

        $this->assertStringContainsString('fi-cl fi-cl-invalid', $component->html());
        $this->assertMatchesRegularExpression('/fi-fo-field-wrp-error-message"[^>]*>\s*The street field is required\./', $component->html());
    }

    // ---------------------------------------------------------------------------------------------------------------
    // Escaping
    // ---------------------------------------------------------------------------------------------------------------

    public function test_labels_and_error_messages_are_escaped(): void
    {
        $html = $this->form([
            Cluster::make([
                TextInput::make('a')
                    ->label('<img src=x onerror=alert(1)>')
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            $fail('Bad value <script>alert("'.$value.'")</script>');
                        },
                    ]),
            ])->label('<b>Group</b>'),
        ])
            ->fillForm(['a' => 'x'])
            ->call('save')
            ->html();

        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('<b>Group</b>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    // ---------------------------------------------------------------------------------------------------------------
    // Stylesheet & package metadata
    // ---------------------------------------------------------------------------------------------------------------

    public function test_bug_the_stylesheet_is_declared_in_a_cascade_layer(): void
    {
        $css = (string) file_get_contents(__DIR__.'/../../resources/dist/clusters.css');

        // Requirement: "plain CSS in a layer that does not fight Filament utilities". Unlayered CSS beats every
        // layered Tailwind utility, so `->extraAttributes(['class' => 'rounded-none'])` can never win.
        $this->assertMatchesRegularExpression('/@layer\s+[\w-]+/', $css);
    }

    public function test_bug_row_dividers_are_drawn_on_the_inline_start_side_in_rtl(): void
    {
        $css = (string) file_get_contents(__DIR__.'/../../resources/dist/clusters.css');

        // `box-shadow: inset 1px 0` is physical (always the LEFT edge). In RTL the first cell sits on the right,
        // so the hairline lands on the outer edge of the later cell instead of between the cells.
        $this->assertMatchesRegularExpression('/\[dir=.?rtl.?\]|:dir\(rtl\)|border-inline-start/', $css);
    }

    public function test_bug_composer_description_is_filled_in(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__.'/../../composer.json'), true);

        $this->assertIsArray($composer);
        $this->assertStringNotContainsString('TODO', (string) $composer['description']);
    }
}
