<?php

namespace App\Filament\Actions;

use App\Filament\Forms\DeviceHasSoftwareForm;
use App\Filament\Forms\SoftwareForm;
use App\Filament\Resources\SoftwareCategoryResource;
use App\Models\DeviceHasSoftware;
use App\Models\Software;
use App\Services\AssetNumberRuleService;
use App\Services\DeviceHasSoftwareService;
use App\Services\FlowHasFormService;
use App\Services\SettingService;
use App\Services\SoftwareService;
use App\Utils\LogUtil;
use App\Utils\NotificationUtil;
use Exception;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SoftwareAction
{
    /**
     * 软件附加到设备按钮.
     */
    public static function createDeviceHasSoftware(?Model $out_software = null): Action
    {
        /* @var Software $out_software */
        return Action::make('附加到设备')
            ->slideOver()
            ->icon('heroicon-m-plus-circle')
            ->form(DeviceHasSoftwareForm::createFromSoftware($out_software))
            ->action(function (array $data, Software $software) use ($out_software) {
                try {
                    if ($out_software) {
                        $software = $out_software;
                    }
                    foreach ($data['device_ids'] as $device_id) {
                        $data['device_id'] = $device_id;
                        $data['software_id'] = $software->getKey();
                        $data['user_id'] = auth()->id();
                        $data['status'] = 0;
                        $device_has_software_service = new DeviceHasSoftwareService();
                        $device_has_software_service->create($data);
                    }
                    NotificationUtil::make(true, '软件已附加到设备');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 创建软件.
     */
    public static function create(): Action
    {
        return Action::make('新增')
            ->slideOver()
            ->icon('heroicon-m-plus')
            ->form(SoftwareForm::createOrEdit())
            ->action(function (array $data) {
                try {
                    $software_service = new SoftwareService();
                    $software_service->create($data);
                    NotificationUtil::make(true, '已新增软件');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 软件脱离设备按钮.
     */
    public static function deleteDeviceHasSoftware(): Action
    {
        return Action::make('脱离')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (DeviceHasSoftware $device_has_software) {
                try {
                    $data = [
                        'user_id' => auth()->id(),
                        'status' => 1,
                    ];
                    $device_has_software->service()->delete($data);
                    NotificationUtil::make(true, '软件已脱离设备');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 配置软件报废流程.
     */
    public static function setRetireFlow(): Action
    {
        return Action::make('配置报废流程')
            ->slideOver()
            ->form(SoftwareForm::setRetireFlow())
            ->action(function (array $data) {
                try {
                    $setting_service = new SettingService();
                    $setting_service->set('software_retire_flow_id', $data['flow_id']);
                    NotificationUtil::make(true, '流程配置成功');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 配置资产编号生成配置.
     */
    public static function setAssetNumberRule(): Action
    {
        return Action::make('配置资产编号自动生成规则')
            ->slideOver()
            ->form(SoftwareForm::setAssetNumberRule())
            ->action(function (array $data) {
                $data['class_name'] = Software::class;
                AssetNumberRuleService::setAutoRule($data);
                NotificationUtil::make(true, '已配置资产编号自动生成规则');
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 配置资产编号生成配置.
     */
    public static function resetAssetNumberRule(): Action
    {
        return Action::make('清除资产编号自动生成规则')
            ->requiresConfirmation()
            ->action(function () {
                AssetNumberRuleService::resetAutoRule(Software::class);
                NotificationUtil::make(true, '已清除编号自动生成规则');
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 强制报废按钮.
     */
    public static function forceRetire(): Action
    {
        return Action::make('强制报废')
            ->requiresConfirmation()
            ->icon('heroicon-m-archive-box-x-mark')
            ->action(function (Software $software) {
                try {
                    $software->service()->retire();
                    NotificationUtil::make(true, '已报废');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 流程报废按钮.
     */
    public static function retire(): Action
    {
        return Action::make('流程报废')
            ->slideOver()
            ->icon('heroicon-m-archive-box-x-mark')
            ->form(SoftwareForm::retire())
            ->action(function (array $data, Software $software) {
                try {
                    $software_retire_flow = $software->service()->getRetireFlow();
                    $asset_number = $software->getAttribute('asset_number');
                    $flow_has_form_service = new FlowHasFormService();
                    $data['flow_id'] = $software_retire_flow->getKey();
                    $data['name'] = '软件报废单 - '.$asset_number;
                    $data['payload'] = $asset_number;
                    $flow_has_form_service->create($data);
                    NotificationUtil::make(true, '已创建表单');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            })
            ->closeModalByClickingAway(false);
    }

    /**
     * 前往软件分类清单.
     */
    public static function toCategories(): Action
    {
        return Action::make('分类')
            ->icon('heroicon-s-square-3-stack-3d')
            ->url(SoftwareCategoryResource::getUrl('index'));
    }

    /**
     * 批量脱离软件按钮.
     */
    public static function batchDeleteDeviceHasSoftware(): BulkAction
    {
        return BulkAction::make('批量脱离')
            ->requiresConfirmation()
            ->icon('heroicon-m-minus-circle')
            ->color('danger')
            ->action(function (Collection $device_has_software) {
                $data = [
                    'user_id' => auth()->id(),
                    'status' => 1,
                ];
                /* @var DeviceHasSoftware $item */
                foreach ($device_has_software as $item) {
                    $item->service()->delete($data);
                }
                NotificationUtil::make(true, '已批量脱离');
            })
            ->closeModalByClickingAway(false);
    }
}
