<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Components\Infolists\RecordChipEntry;
use App\Filament\Concerns\EditsRecordFieldsInline;
use App\Filament\Concerns\RendersRecordSplitView;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\CompanyResource\RelationManagers\NotesRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\PeopleRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\TasksRelationManager;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Relaticle\ActivityLog\Filament\RelationManagers\ActivityLogRelationManager;
use Relaticle\CustomFields\Facades\CustomFields;

final class ViewCompany extends ViewRecord
{
    use EditsRecordFieldsInline;
    use RendersRecordSplitView;

    protected static string $resource = CompanyResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->columns($this->stackedInlineColumns())
            ->schema([
                Section::make()
                    ->key('companyDetails')
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
                            RecordChipEntry::make('accountOwner.name')
                                ->chipSize('sm')
                                ->label(__('filament/resources/company.pages.view.infolist.fields.account_owner.label')),
                            'account_owner_id',
                        ),
                        ...$this->inlineEditableCustomFieldEntries(),
                        CustomFields::infolist()
                            ->forSchema($schema)
                            ->except($this->inlineCustomFieldCodes())
                            ->build()
                            ->columns($this->stackedInlineColumns())
                            ->columnSpan($this->stackedInlineColumnSpan()),
                        TextEntry::make('created_by')
                            ->label(__('filament/resources/company.pages.view.infolist.fields.creator.label'))
                            ->columnSpan($this->stackedInlineColumnSpan()),
                        TextEntry::make('created_at')
                            ->label(__('filament/resources/company.pages.view.infolist.fields.created_at.label'))
                            ->dateTime()
                            ->columnSpan($this->stackedInlineColumnSpan()),
                        TextEntry::make('updated_at')
                            ->label(__('filament/resources/company.pages.view.infolist.fields.updated_at.label'))
                            ->dateTime()
                            ->columnSpan($this->stackedInlineColumnSpan()),
                    ])
                    ->footer($this->recordDetailsOverflowToggle())
                    ->columns($this->stackedInlineColumns())
                    ->columnSpan($this->stackedInlineColumnSpan())
                    ->compact(),
            ]);
    }

    public function getRelationManagers(): array
    {
        return [
            PeopleRelationManager::class,
            TasksRelationManager::class,
            NotesRelationManager::class,
            ActivityLogRelationManager::class,
        ];
    }

    /**
     * @return list<string>
     */
    protected function inlineEditEagerLoads(): array
    {
        return ['accountOwner', 'creator', 'customFieldValues.customField.options'];
    }

    protected function recordOverflowTranslationPrefix(): string
    {
        return 'filament/resources/company';
    }
}
