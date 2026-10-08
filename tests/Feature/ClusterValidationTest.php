<?php

declare(strict_types=1);

namespace Asignua\FilamentClusters\Tests\Feature;

use Asignua\FilamentClusters\Cluster;
use Asignua\FilamentClusters\Tests\TestCase;
use Filament\Forms\Components\TextInput;
use Livewire\Livewire;
use Workbench\App\Livewire\SingleClusterForm;

class ClusterValidationTest extends TestCase
{
    /**
     * @param array<int, mixed> $components
     */
    private function form(array $components): \Livewire\Features\SupportTesting\Testable
    {
        SingleClusterForm::$components = $components;

        return Livewire::test(SingleClusterForm::class);
    }

    private function nameCluster(): Cluster
    {
        return Cluster::make([
            TextInput::make('first_name')->label('First name')->required(),
            TextInput::make('last_name')->label('Last name')->required()->maxLength(5),
        ])->label('Name');
    }

    public function test_state_is_saved_under_the_childrens_own_keys(): void
    {
        $this->form([$this->nameCluster()])
            ->fillForm(['first_name' => 'Ada', 'last_name' => 'Byron'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertSet('saved.first_name', 'Ada')
            ->assertSet('saved.last_name', 'Byron')
            ->assertSet('saved', ['first_name' => 'Ada', 'last_name' => 'Byron']);
    }

    public function test_each_child_is_validated_against_its_own_path(): void
    {
        $this->form([$this->nameCluster()])
            ->fillForm(['first_name' => '', 'last_name' => 'Lovelace'])
            ->call('save')
            ->assertHasFormErrors(['first_name' => 'required', 'last_name' => 'max']);
    }

    public function test_the_errors_of_all_children_are_listed_under_the_cluster(): void
    {
        $component = $this->form([$this->nameCluster()])
            ->fillForm(['first_name' => '', 'last_name' => ''])
            ->call('save');

        $html = $component->html();

        $this->assertStringContainsString('The first name field is required.', $html);
        $this->assertStringContainsString('The last name field is required.', $html);
        $this->assertSame(1, substr_count($html, 'class="fi-fo-field-wrp-error-list"'));
        $this->assertSame(2, substr_count($html, 'class="fi-fo-field-wrp-error-message"'));
    }

    public function test_a_single_error_is_a_plain_message(): void
    {
        $html = $this->form([$this->nameCluster()])
            ->fillForm(['first_name' => 'Ada', 'last_name' => 'Lovelace'])
            ->call('save')
            ->html();

        $this->assertStringContainsString('<p', $html);
        $this->assertStringContainsString('data-validation-error', $html);
        $this->assertSame(1, substr_count($html, 'class="fi-fo-field-wrp-error-message"'));
        $this->assertSame(0, substr_count($html, 'fi-fo-field-wrp-error-list'));
    }

    public function test_the_cluster_is_marked_invalid_only_while_a_child_is(): void
    {
        $component = $this->form([$this->nameCluster()]);

        $this->assertStringNotContainsString('fi-cl-invalid', $component->html());

        $component->fillForm(['first_name' => '', 'last_name' => 'x'])->call('save');

        $this->assertStringContainsString('fi-cl fi-cl-invalid', $component->html());
        $this->assertStringContainsString('aria-invalid="true"', $component->html());

        $component->fillForm(['first_name' => 'Ada', 'last_name' => 'x'])->call('save');

        $this->assertStringNotContainsString('fi-cl-invalid', $component->html());
    }

    public function test_only_the_invalid_child_is_marked(): void
    {
        $html = $this->form([$this->nameCluster()])
            ->fillForm(['first_name' => 'Ada', 'last_name' => 'Lovelace'])
            ->call('save')
            ->html();

        $this->assertSame(1, preg_match_all('/class="[^"]*(?<![\w-])fi-input-wrp(?![\w-])[^"]*(?<![\w-])fi-invalid(?![\w-])/', $html));
    }

    public function test_cross_field_rules_work_between_children(): void
    {
        $cluster = Cluster::make([
            TextInput::make('min')->numeric(),
            TextInput::make('max')->numeric()->gte('min'),
        ])->label('Range');

        $this->form([$cluster])
            ->fillForm(['min' => 10, 'max' => 5])
            ->call('save')
            ->assertHasFormErrors(['max' => 'gte']);

        $this->form([$cluster])
            ->fillForm(['min' => 5, 'max' => 10])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_a_cluster_inside_a_nested_state_path_keeps_the_prefix(): void
    {
        $cluster = Cluster::make([TextInput::make('city')->required(), TextInput::make('zip')])->label('Place');

        SingleClusterForm::$components = [
            \Filament\Schemas\Components\Section::make()->statePath('address')->schema([$cluster]),
        ];

        Livewire::test(SingleClusterForm::class)
            ->fillForm(['address' => ['city' => '', 'zip' => '1']])
            ->call('save')
            ->assertHasFormErrors(['address.city' => 'required']);
    }

    public function test_a_hidden_cluster_child_is_not_validated(): void
    {
        $cluster = Cluster::make([
            TextInput::make('a'),
            TextInput::make('b')->required()->hidden(),
        ])->label('Pair');

        $this->form([$cluster])
            ->fillForm(['a' => 'x'])
            ->call('save')
            ->assertHasNoFormErrors();
    }
}
