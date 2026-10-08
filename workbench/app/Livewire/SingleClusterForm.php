<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * A form whose components the test sets through the static property, so one component class serves every test.
 */
class SingleClusterForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<int, mixed> */
    public static array $components = [];

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array<string, mixed> */
    public array $saved = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(self::$components)->statePath('data');
    }

    public function save(): void
    {
        $this->saved = $this->form->getState();
    }

    public function render(): View
    {
        return view('cluster-form');
    }
}
