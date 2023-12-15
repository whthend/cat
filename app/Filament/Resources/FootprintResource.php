<?php

namespace App\Filament\Resources;

use App\Enums\FootprintEnum;
use App\Filament\Resources\FootprintResource\Pages\Index;
use App\Filament\Resources\FootprintResource\Pages\View;
use App\Models\Footprint;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FootprintResource extends Resource
{
    protected static ?string $model = Footprint::class;

    protected static ?string $navigationIcon = 'heroicon-m-document-text';

    protected static ?string $navigationGroup = '日志';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = '脚印';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getRecordSubNavigation(Page $page): array
    {
        $navigation_items = [
            Index::class,
            View::class,
        ];

        return $page->generateNavigationItems($navigation_items);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->toggleable()
                    ->label('ID'),
                TextColumn::make('creator.name')
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->color('gray')
                    ->label('操作人'),
                TextColumn::make('action')
                    ->formatStateUsing(function ($state) {
                        return FootprintEnum::actionText($state);
                    })
                    ->badge()
                    ->color(function ($state) {
                        return FootprintEnum::actionColor($state);
                    })
                    ->label('操作'),
                TextColumn::make('model_class')
                    ->searchable()
                    ->toggleable()
                    ->label('模型'),
                TextColumn::make('model_id')
                    ->searchable()
                    ->toggleable()
                    ->label('记录 ID'),
                TextColumn::make('created_at')
                    ->alignRight()
                    ->label('时间'),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->multiple()
                    ->options(FootprintEnum::allActionText())
                    ->label('操作'),
            ])
            ->actions([

            ])
            ->bulkActions([

            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Group::make()->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make()
                                ->schema([
                                    Group::make([
                                        TextEntry::make('creator.name')
                                            ->label('操作人')
                                            ->badge(),
                                        TextEntry::make('model_class')
                                            ->label('模型'),
                                        TextEntry::make('created_at')
                                            ->label('时间'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('action')
                                            ->formatStateUsing(function ($state) {
                                                return FootprintEnum::actionText($state);
                                            })
                                            ->badge()
                                            ->color(function ($state) {
                                                return FootprintEnum::actionColor($state);
                                            })
                                            ->label('操作'),
                                        TextEntry::make('model_id')
                                            ->label('模型 ID'),
                                    ]),
                                ]),
                        ]),
                    ]),
            ])->columnSpan(2),
            Group::make()->schema([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                Group::make([
                                    TextEntry::make('before')
                                        ->formatStateUsing(function ($state) {
                                            $state = json_encode(json_decode($state), JSON_PRETTY_PRINT);

                                            return <<<EOF
```json
$state
```
EOF;
                                        })
                                        ->markdown()
                                        ->label('原始值'),
                                ]),
                            ]),
                    ]),
            ])
                ->columnSpan(1),
            Group::make()->schema([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                Group::make([
                                    TextEntry::make('after')
                                        ->formatStateUsing(function ($state) {
                                            $state = json_encode(json_decode($state), JSON_PRETTY_PRINT);

                                            return <<<EOF
```json
$state
```
EOF;
                                        })
                                        ->markdown()
                                        ->label('修改内容')]),
                            ]),
                    ]),
            ])
                ->columnSpan(1), ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderByDesc('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Index::route('/'),
            'view' => View::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}