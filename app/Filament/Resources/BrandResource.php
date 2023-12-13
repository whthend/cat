<?php

namespace App\Filament\Resources;

use App\Filament\Actions\BrandAction;
use App\Filament\Forms\BrandForm;
use App\Filament\Imports\BrandImporter;
use App\Filament\Resources\BrandResource\Pages\Edit;
use App\Filament\Resources\BrandResource\Pages\Index;
use App\Filament\Resources\BrandResource\Pages\View;
use App\Models\Brand;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class BrandResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Brand::class;

    protected static ?string $modelLabel = '品牌';

    protected static ?string $navigationIcon = 'heroicon-s-tag';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = '基础数据';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getRecordSubNavigation(Page $page): array
    {
        $navigation = [
            Index::class,
            View::class,
            Edit::class,
        ];
        $can_update_brand = auth()->user()->can('update_brand');
        if (! $can_update_brand) {
            unset($navigation[2]);
        }

        return $page->generateNavigationItems($navigation);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'import',
            'export',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(BrandForm::createOrEdit());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名称'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // 删除
                BrandAction::delete()
                    ->visible(function () {
                        return auth()->user()->can('delete_brand');
                    }),
            ])
            ->bulkActions([

            ])
            ->emptyStateActions([

            ])
            ->headerActions([
                // 导入
                ImportAction::make()
                    ->importer(BrandImporter::class)
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->label('导入')
                    ->visible(function () {
                        return auth()->user()->can('import_brand');
                    }),
                // 导出
                ExportAction::make()
                    ->label('导出')
                    ->visible(function () {
                        return auth()->user()->can('export_brand');
                    }),
                // 创建
                BrandAction::create()
                    ->visible(function () {
                        return auth()->user()->can('create_brand');
                    }),
            ])
            ->heading('品牌');
    }

    public static function getPages(): array
    {
        return [
            'index' => Index::route('/'),
            'view' => View::route('/{record}'),
            'edit' => Edit::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
