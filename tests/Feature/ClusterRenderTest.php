<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters\Tests\Feature;

use Asignua\FilamentClusters\Cluster;
use Asignua\FilamentClusters\Tests\TestCase;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Livewire\Livewire;
use Workbench\App\Livewire\ClusterForm;
use Workbench\App\Livewire\SingleClusterForm;

class ClusterRenderTest extends TestCase
{
    /**
     * @param array<int, mixed> $components
     */
    private function render(array $components): string
    {
        SingleClusterForm::$components = $components;

        return Livewire::test(SingleClusterForm::class)->html();
    }

    public function test_children_keep_their_own_state_paths(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('first'), TextInput::make('last')])->label('Name'),
        ]);

        $this->assertStringContainsString('wire:model="data.first"', $html);
        $this->assertStringContainsString('wire:model="data.last"', $html);
        $this->assertStringNotContainsString('data.cluster', $html);
    }

    public function test_it_renders_one_group_with_a_shared_label(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('first'), TextInput::make('last')])->label('Name'),
        ]);

        $this->assertSame(1, substr_count($html, 'role="group"'));
        $this->assertSame(1, substr_count($html, 'class="fi-fo-field-label"'));
        $this->assertStringContainsString('Name', $html);
        $this->assertMatchesRegularExpression('/aria-labelledby="data\.first-cluster-label"/', $html);
        $this->assertStringContainsString('id="data.first-cluster-label"', $html);
    }

    public function test_children_are_labelled_for_screen_readers_only(): void
    {
        $html = $this->render([
            Cluster::make([
                TextInput::make('first')->label('First'),
                TextInput::make('last')->label('Last'),
            ])->label('Name'),
        ]);

        $this->assertStringContainsString('class="fi-sr-only">Name: First</label>', $html);
        $this->assertStringContainsString('class="fi-sr-only">Name: Last</label>', $html);
        $this->assertSame(1, substr_count($html, 'class="fi-fo-field-label"'));
    }

    public function test_hint_and_helper_text_are_rendered_once(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])
                ->label('Pair')
                ->hint('A hint')
                ->helperText('Some help'),
        ]);

        $this->assertSame(1, substr_count($html, 'A hint'));
        $this->assertSame(1, substr_count($html, 'Some help'));
    }

    public function test_the_required_marker_follows_the_children(): void
    {
        $required = $this->render([
            Cluster::make([TextInput::make('a')->required(), TextInput::make('b')])->label('Pair'),
        ]);
        $optional = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair'),
        ]);

        $this->assertStringContainsString('fi-fo-field-label-required-mark', $required);
        $this->assertStringNotContainsString('fi-fo-field-label-required-mark', $optional);
    }

    public function test_the_required_marker_can_be_forced_either_way(): void
    {
        $forcedOn = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->markAsRequired(),
        ]);
        $forcedOff = $this->render([
            Cluster::make([TextInput::make('a')->required()])->label('Pair')->markAsRequired(false),
        ]);

        $this->assertStringContainsString('fi-fo-field-label-required-mark', $forcedOn);
        $this->assertStringNotContainsString('fi-fo-field-label-required-mark', $forcedOff);
    }

    public function test_columns_default_to_one_per_child(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b'), TextInput::make('c')])->label('Three'),
        ]);

        $this->assertStringContainsString('--cols-default: repeat(3, minmax(0, 1fr))', $html);
        $this->assertStringContainsString('data-cl-default="row"', $html);
    }

    public function test_an_integer_means_that_many_columns_on_every_screen(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Wide')->columns(5),
        ]);

        $this->assertStringContainsString('--cols-default: repeat(5, minmax(0, 1fr))', $html);
        $this->assertStringNotContainsString('--cols-lg', $html);
    }

    public function test_children_can_span_columns(): void
    {
        $html = $this->render([
            Cluster::make([
                Select::make('currency')->options(['EUR' => 'EUR']),
                TextInput::make('amount')->columnSpan(['default' => 3]),
            ])->label('Money')->columns(4),
        ]);

        $this->assertStringContainsString('--col-span-default: span 3 / span 3', $html);
    }

    public function test_breakpoint_columns_are_supported(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b'), TextInput::make('c'), TextInput::make('d')])
                ->label('Address')
                ->columns(['default' => 1, 'sm' => 2]),
        ]);

        $this->assertStringContainsString('--cols-default: repeat(1, minmax(0, 1fr))', $html);
        $this->assertStringContainsString('--cols-sm: repeat(2, minmax(0, 1fr))', $html);
        $this->assertStringContainsString('data-cl-default="col"', $html);
        $this->assertStringContainsString('data-cl-sm="grid"', $html);
    }

    public function test_stack_below_stacks_on_small_screens_only(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->stackBelow('md'),
        ]);

        $this->assertStringContainsString('--cols-default: repeat(1, minmax(0, 1fr))', $html);
        $this->assertStringContainsString('--cols-md: repeat(2, minmax(0, 1fr))', $html);
        $this->assertStringContainsString('data-cl-default="col"', $html);
        $this->assertStringContainsString('data-cl-md="row"', $html);
    }

    public function test_one_column_makes_a_vertical_cluster(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Stack')->columns(1),
        ]);

        $this->assertStringContainsString('data-cl-default="col"', $html);
    }

    public function test_hidden_children_do_not_count_as_columns(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')->hidden(), TextInput::make('c')])->label('Pair'),
        ]);

        $this->assertStringContainsString('--cols-default: repeat(2, minmax(0, 1fr))', $html);
        $this->assertStringNotContainsString('wire:model="data.b"', $html);
    }

    public function test_the_children_use_filaments_own_input_wrapper(): void
    {
        $html = $this->render([
            Cluster::make([
                TextInput::make('street')->prefix('St.'),
                TextInput::make('house')->suffix('no.'),
            ])->label('Address'),
        ]);

        $this->assertSame(2, preg_match_all('/class="[^"]*(?<![\w-])fi-input-wrp(?![\w-])[^"]*"/', $html));
        $this->assertStringContainsString('fi-input-wrp-prefix', $html);
        $this->assertStringContainsString('fi-input-wrp-suffix', $html);
        $this->assertStringContainsString('St.', $html);
        $this->assertStringContainsString('no.', $html);
    }

    public function test_the_supported_field_types_render_inside_a_cluster(): void
    {
        $html = $this->render([
            Cluster::make([
                Select::make('currency')->options(['EUR' => 'EUR']),
                TextInput::make('amount'),
                DatePicker::make('day'),
                TimePicker::make('time'),
                ColorPicker::make('color'),
            ])->label('Everything'),
        ]);

        foreach (['currency', 'amount', 'day', 'time', 'color'] as $name) {
            $this->assertStringContainsString("data.{$name}", $html);
        }

        $this->assertSame(5, preg_match_all('/class="[^"]*(?<![\w-])fi-cl-cell(?![\w-])/', $html));
    }

    public function test_a_cluster_of_disabled_fields_is_drawn_disabled(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a')->disabled(), TextInput::make('b')->disabled()])->label('Locked'),
            Cluster::make([TextInput::make('c')->disabled(), TextInput::make('d')])->label('Mixed'),
        ]);

        $this->assertSame(1, substr_count($html, 'fi-cl fi-cl-disabled'));
    }

    public function test_the_demo_form_renders_every_cluster(): void
    {
        $html = Livewire::test(ClusterForm::class)->html();

        $this->assertSame(6, substr_count($html, 'role="group"'));
    }

    public function test_the_class_can_be_extended_with_extra_attributes(): void
    {
        $html = $this->render([
            Cluster::make([TextInput::make('a'), TextInput::make('b')])->label('Pair')->extraAttributes(['data-test' => 'yes']),
        ]);

        $this->assertStringContainsString('data-test="yes"', $html);
    }
}
