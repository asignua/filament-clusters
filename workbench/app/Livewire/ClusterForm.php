<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Asignua\FilamentClusters\Cluster;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ClusterForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

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
        return $schema
            ->components([
                Cluster::make([
                    TextInput::make('first_name')->label('First')->required(),
                    TextInput::make('last_name')->label('Last')->required()->maxLength(10),
                ])->label('Name')->helperText('As in the passport')->hint('Legal name'),

                Cluster::make([
                    Select::make('currency')->options(['EUR' => 'EUR', 'USD' => 'USD'])->native(false)->default('EUR'),
                    TextInput::make('amount')->numeric()->minValue(1)->columnSpan(3),
                ])->label('Price')->columns(4),

                Cluster::make([
                    DatePicker::make('from'),
                    TimePicker::make('at'),
                    ColorPicker::make('color'),
                ])->label('Slot')->stackBelow('md'),

                Cluster::make([
                    TextInput::make('street')->prefix('St.'),
                    TextInput::make('house')->suffix('no.'),
                    TextInput::make('zip'),
                    TextInput::make('city'),
                ])->label('Address')->columns(['default' => 1, 'sm' => 2]),

                Cluster::make([
                    TextInput::make('min')->numeric(),
                    TextInput::make('max')->numeric()->gte('min'),
                ])->label('Range')->markAsRequired(),

                Cluster::make([
                    TextInput::make('locked_a')->disabled(),
                    TextInput::make('locked_b')->disabled(),
                ])->label('Locked'),
            ])
            ->statePath('data');
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
