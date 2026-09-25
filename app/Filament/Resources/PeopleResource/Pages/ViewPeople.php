<?php

declare(strict_types=1);

namespace App\Filament\Resources\PeopleResource\Pages;

use App\Filament\Components\Infolists\RecordChipEntry;
use App\Filament\Concerns\EditsRecordFieldsInline;
use App\Filament\Concerns\RendersRecordSplitView;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\PeopleResource;
use App\Models\People;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Relaticle\CustomFields\Facades\CustomFields;

final class ViewPeople extends ViewRecord
{
    use EditsRecordFieldsInline;
    use RendersRecordSplitView;

    protected static string $resource = PeopleResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->columns($this->stackedInlineColumns())
            ->schema([
                Section::make()
                    ->key('personDetails')
                    ->inlineLabel()
                    ->headerActions([
                        $this->recordOverflowActions(),
                    ])
                    ->schema([
                        $this->makeInlineEditable(
                            RecordChipEntry::make('name')
                                ->hiddenLabel()
                                ->inlineLabel(false)
                                ->chipSize('md'),
                            'name',
                        ),
                        $this->makeInlineEditable(
                            RecordChipEntry::make('company.name')
                                ->label(__('filament/resources/person.pages.view.infolist.fields.company.label'))
                                ->color('primary')
                                ->url(fn (People $record): ?string => $record->company ? CompanyResource::getUrl('view', [$record->company]) : null),
                            'company_id',
                        ),
                        ...$this->inlineEditableCustomFieldEntries(),
                        CustomFields::infolist()
                            ->forSchema($schema)
                            ->except($this->inlineCustomFieldCodes())
                            ->build()
                            ->columns($this->stackedInlineColumns())
                            ->columnSpan($this->stackedInlineColumnSpan()),
                    ])
                    ->footer($this->recordDetailsOverflowToggle())
                    ->columns($this->stackedInlineColumns())
                    ->columnSpan($this->stackedInlineColumnSpan())
                    ->compact(),
            ]);
    }

    /**
     * @return list<string>
     */
    protected function inlineEditEagerLoads(): array
    {
        return ['company', 'customFieldValues.customField.options'];
    }

    protected function recordOverflowTranslationPrefix(): string
    {
        return 'filament/resources/person';
    }
}
