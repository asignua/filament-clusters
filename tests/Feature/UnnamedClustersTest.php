<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters\Tests\Feature;

use Asignua\FilamentClusters\Cluster;
use Asignua\FilamentClusters\Tests\TestCase;
use Filament\Forms\Components\TextInput;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Workbench\App\Livewire\SingleClusterForm;

class UnnamedClustersTest extends TestCase
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
     * @return array<int, Cluster>
     */
    private function two(): array
    {
        return [
            Cluster::make([TextInput::make('first_name')->required(), TextInput::make('last_name')])->label('Name'),
            Cluster::make([TextInput::make('city')->required(), TextInput::make('zip')->maxLength(3)])->label('Place'),
        ];
    }

    public function test_two_unnamed_clusters_get_distinct_names(): void
    {
        [$a, $b] = $this->two();

        $this->assertNotSame($a->getName(), $b->getName());
    }

    public function test_the_generated_name_is_stable_between_instances_in_the_same_position(): void
    {
        // Livewire rebuilds the schema on every request: the same code must give the same name.
        $first = $this->two();
        $second = $this->two();

        $this->assertSame($first[0]->getName(), $second[0]->getName());
        $this->assertSame($first[1]->getName(), $second[1]->getName());
    }

    public function test_state_of_two_unnamed_clusters_is_independent(): void
    {
        $this->form($this->two())
            ->fillForm(['first_name' => 'Ada', 'last_name' => 'B', 'city' => 'Lviv', 'zip' => '79'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('saved', ['first_name' => 'Ada', 'last_name' => 'B', 'city' => 'Lviv', 'zip' => '79']);
    }

    public function test_errors_are_mapped_to_the_right_cluster(): void
    {
        $html = $this->form($this->two())
            ->fillForm(['first_name' => 'Ada', 'city' => '', 'zip' => '12345'])
            ->call('save')
            ->assertHasFormErrors(['city' => 'required', 'zip' => 'max'])
            ->assertHasNoFormErrors(['first_name'])
            ->html();

        $this->assertSame(1, preg_match_all('/class="[^"]*\bfi-cl-invalid\b/', $html));
        $this->assertSame(1, preg_match_all('/<div[^>]*role="group"[^>]*aria-invalid="true"/', $html));
        $this->assertSame(2, preg_match_all('/<div[^>]*role="group"/', $html));
        $this->assertStringContainsString('The city field is required.', $html);
        $this->assertStringNotContainsString('The first name field is required.', $html);
    }

    public function test_explicit_name_still_wins(): void
    {
        $this->assertSame('mine', Cluster::make('mine')->getName());
    }

    public function test_an_integer_for_columns_equals_the_default_breakpoint(): void
    {
        $int = Cluster::make([TextInput::make('a'), TextInput::make('b')])->columns(3);
        $array = Cluster::make([TextInput::make('a'), TextInput::make('b')])->columns(['default' => 3]);

        $this->assertSame($array->getAllColumns(), $int->getAllColumns());
        $this->assertSame(3, $int->getAllColumns()['default']);
        $this->assertArrayNotHasKey('lg', $int->getAllColumns());
    }

    public function test_an_integer_with_stack_below_keeps_the_wide_layout_from_the_breakpoint(): void
    {
        $columns = Cluster::make([TextInput::make('a'), TextInput::make('b')])->columns(2)->stackBelow('md')->getAllColumns();

        $this->assertSame(1, $columns['default']);
        $this->assertSame(2, $columns['md']);
    }
}
