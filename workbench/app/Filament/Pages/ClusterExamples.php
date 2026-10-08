<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Pages;

use Asignua\FilamentClusters\Cluster;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;

/**
 * A hand-testing page: several Cluster examples in one form. Submit with the required names empty to see the errors.
 *
 * @property-read Schema $form
 */
class ClusterExamples extends Page
{
    protected static ?string $slug = 'cluster-examples';

    protected static ?string $title = 'Cluster examples';

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
                    TextInput::make('first_name')->label('First name')->required(),
                    TextInput::make('last_name')->label('Last name')->required(),
                ])->label('Name (both required: submit empty to see errors)')->helperText('As in the passport')->hint('Legal name'),

                Cluster::make([
                    TextInput::make('street')->columnSpan(['default' => 1, 'sm' => 3]),
                    TextInput::make('house')->columnSpan(['default' => 1, 'sm' => 1]),
                    TextInput::make('apartment')->columnSpan(['default' => 1, 'sm' => 1]),
                ])->label('Address line')->columns(['default' => 1, 'sm' => 5]),

                Cluster::make([
                    DatePicker::make('from'),
                    DatePicker::make('to')->afterOrEqual('from'),
                ])->label('Period'),

                Cluster::make([
                    Select::make('currency')->options(['EUR' => 'EUR', 'USD' => 'USD', 'UAH' => 'UAH'])->native(false)->default('EUR'),
                    TextInput::make('amount')->numeric()->minValue(1)->columnSpan(['default' => 3]),
                ])->label('Price')->columns(4),

                Cluster::make([
                    TextInput::make('weight')->numeric()->suffix('kg'),
                    TextInput::make('handle')->prefix('@'),
                ])->label('Prefix and suffix'),

                Cluster::make([
                    TextInput::make('street_2'),
                    TextInput::make('house_2'),
                    TextInput::make('zip'),
                    TextInput::make('city'),
                ])->label('Two rows (columns(2))')->columns(2),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('Save')->submit('save')])]),
            Section::make('Saved state')->components([Text::make(fn (): string => json_encode($this->saved, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '[]')]),
        ]);
    }

    public function save(): void
    {
        $this->saved = $this->form->getState();

        Notification::make()->title('Saved')->success()->send();
    }
}
