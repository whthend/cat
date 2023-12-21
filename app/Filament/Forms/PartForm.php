<?php

namespace App\Filament\Forms;

use App\Models\Part;
use App\Services\AssetNumberRuleService;
use App\Services\FlowService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Ramsey\Uuid\Uuid;

class PartForm
{
    /**
     * 创建或编辑.
     */
    public static function createOrEdit(): array
    {
        return [
            TextInput::make('asset_number')
                ->maxLength(255)
                ->label(__('cat.asset_number'))
                ->required(function () {
                    return ! AssetNumberRuleService::isAuto(Part::class);
                })
                ->readOnly(function () {
                    return AssetNumberRuleService::isAuto(Part::class);
                })
                ->hintAction(
                    Action::make(__('cat.form.create_asset_number_helper'))
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->visible(function () {
                            return AssetNumberRuleService::isAuto(Part::class);
                        })
                ),
            Select::make('category_id')
                ->relationship('category', 'name')
                ->label(__('cat.category'))
                ->searchable()
                ->preload()
                ->createOptionForm(PartCategoryForm::createOrEdit())
                ->required(),
            Select::make('brand_id')
                ->relationship('brand', 'name')
                ->label(__('cat.brand'))
                ->searchable()
                ->preload()
                ->createOptionForm(BrandForm::createOrEdit())
                ->required(),
            TextInput::make('sn')
                ->maxLength(255)
                ->label(__('cat.sn')),
            TextInput::make('specification')
                ->maxLength(255)
                ->label(__('cat.specification')),
            FileUpload::make('image')
                ->label(__('cat.image'))
                ->directory('parts')
                ->getUploadedFileNameForStorageUsing(
                    function (TemporaryUploadedFile $file) {
                        return Uuid::uuid4().'.'.$file->getClientOriginalExtension();
                    }
                ),
            Textarea::make('description')
                ->label(__('cat.description')),
            Repeater::make('additional')
                ->schema([
                    TextInput::make('name')
                        ->label(__('cat.name')),
                    TextInput::make('text')
                        ->label(__('cat.text')),
                ])
                ->defaultItems(0)
                ->label(__('cat.additional')),
        ];
    }

    /**
     * 配置配件废弃流程.
     */
    public static function setRetireFlow(): array
    {
        return [
            Select::make('flow_id')
                ->options(FlowService::pluckOptions())
                ->required()
                ->label(__('cat.flow')),
        ];
    }

    /**
     * 配置资产编号自动生成规则.
     */
    public static function setAssetNumberRule(): array
    {
        return [
            Select::make('asset_number_rule_id')
                ->label(__('cat.asset_number_rule'))
                ->options(AssetNumberRuleService::pluckOptions())
                ->required()
                ->default(AssetNumberRuleService::getAutoRule(Part::class)?->getAttribute('id')),
            Checkbox::make('is_auto')
                ->label(__('cat.is_auto'))
                ->default(AssetNumberRuleService::getAutoRule(Part::class)?->getAttribute('is_auto')),
        ];
    }

    /**
     * 流程废弃.
     */
    public static function retire(): array
    {
        return [
            TextInput::make('comment')
                ->label(__('cat.comment'))
                ->required(),
        ];
    }
}
