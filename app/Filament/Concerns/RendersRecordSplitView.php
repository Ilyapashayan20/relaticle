<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Js;
use Override;

/**
 * @mixin ViewRecord
 */
trait RendersRecordSplitView
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Group::make([
                        $this->getInfolistContentComponent(),
                    ])
                        ->extraAttributes([
                            'class' => 'fi-record-details-rail',
                            'x-init' => '$el.querySelectorAll(\'.fi-in-entry-label, .fi-in-entry-content\').forEach((node) => { if (node.closest(\'[data-inline-field="name"]\') || node.title || node.scrollWidth <= node.clientWidth + 1) { return } node.title = node.textContent.trim() })',
                        ])
                        ->grow(false),
                    Group::make([
                        $this->getRelationManagersContentComponent(),
                    ])
                        ->extraAttributes(['class' => 'fi-record-work-pane']),
                ])
                    ->from('lg')
                    ->dense()
                    ->extraAttributes(['class' => 'fi-record-split']),
            ]);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'fi-record-split-page',
        ];
    }

    #[Override]
    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function recordOverflowActions(): ActionGroup
    {
        $resource = static::getResource();
        $prefix = $this->recordOverflowTranslationPrefix();

        return ActionGroup::make([
            ActionGroup::make([
                Action::make('copyPageUrl')
                    ->label(__("{$prefix}.pages.view.actions.copy_page_url.label"))
                    ->icon('heroicon-o-clipboard-document')
                    ->action(function (Model $record) use ($resource): void {
                        $jsUrl = Js::from($resource::getUrl('view', [$record]));
                        $this->js("
                            navigator.clipboard.writeText({$jsUrl}).then(() => {
                                new FilamentNotification()
                                    .title('URL copied to clipboard')
                                    .success()
                                    .send()
                            })
                        ");
                    }),
                Action::make('copyRecordId')
                    ->label(__("{$prefix}.pages.view.actions.copy_record_id.label"))
                    ->icon('heroicon-o-clipboard-document')
                    ->action(function (Model $record): void {
                        $jsId = Js::from((string) $record->getKey());
                        $this->js("
                            navigator.clipboard.writeText({$jsId}).then(() => {
                                new FilamentNotification()
                                    .title('Record ID copied to clipboard')
                                    .success()
                                    .send()
                            })
                        ");
                    }),
            ])->dropdown(false),
            DeleteAction::make(),
        ])
            ->iconButton()
            ->color('gray')
            ->size(Size::ExtraSmall)
            ->dropdownPlacement('bottom-end')
            ->extraAttributes(['class' => 'fi-record-details-more']);
    }

    abstract protected function recordOverflowTranslationPrefix(): string;
}
