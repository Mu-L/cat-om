<?php

namespace App\Filament\Actions;

use App\Filament\Forms\PartForm;
use App\Models\DeviceHasPart;
use App\Models\Part;
use App\Services\AssetNumberRuleService;
use App\Services\DeviceService;
use App\Services\FlowService;
use App\Services\PartCategoryService;
use App\Services\PartService;
use App\Services\SettingService;
use App\Utils\LogUtil;
use App\Utils\NotificationUtil;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class PartAction
{
    /**
     * 创建设备分类按钮.
     *
     * @return Action
     */
    public static function createPartCategory(): Action
    {
        return Action::make('新增')
            ->slideOver()
            ->icon('heroicon-m-plus')
            ->form([
                TextInput::make('name')
                    ->label('名称')
                    ->required(),
            ])
            ->action(function (array $data) {
                try {
                    $part_category_service = new PartCategoryService();
                    $part_category_service->create($data);
                    NotificationUtil::make(true, '已创建配件分类');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }

    /**
     * 创建配件.
     *
     * @return Action
     */
    public static function createPart(): Action
    {
        return Action::make('新增')
            ->slideOver()
            ->icon('heroicon-m-plus')
            ->form(PartForm::createOrEditPart())
            ->action(function (array $data) {
                try {
                    $device_service = new PartService();
                    $device_service->create($data);
                    NotificationUtil::make(true, '已新增配件');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }

    /**
     * 创建配件-设备按钮.
     *
     * @param Model|null $out_part
     * @return Action
     */
    public static function createDeviceHasPart(Model $out_part = null): Action
    {
        return Action::make('附加到设备')
            ->form([
                Select::make('device_id')
                    ->options(DeviceService::pluckOptions())
                    ->searchable()
                    ->label('设备')
            ])
            ->action(function (array $data, Part $part) use ($out_part) {
                try {
                    if ($out_part) {
                        $part = $out_part;
                    }
                    $data = [
                        'device_id' => $data['device_id'],
                        'user_id' => auth()->id(),
                        'status' => '附加',
                    ];
                    $part->service()->createHasPart($data);
                    NotificationUtil::make(true, '配件已附加到设备');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }

    /**
     * 配件脱离设备按钮.
     *
     * @return Action
     */
    public static function deleteDeviceHasPart(): Action
    {
        return Action::make('脱离')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (DeviceHasPart $device_has_part) {
                try {
                    $data = [
                        'user_id' => auth()->id(),
                        'status' => '脱离'
                    ];
                    $device_has_part->service()->delete($data);
                    NotificationUtil::make(true, '配件已脱离设备');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }

    /**
     * 绑定配件报废流程.
     *
     * @return Action
     */
    public static function setPartDeleteFlowId(): Action
    {
        return Action::make('配置报废流程')
            ->form([
                Select::make('flow_id')
                    ->options(FlowService::pluckOptions())
                    ->required()
                    ->label('流程')
            ])
            ->action(function (array $data) {
                try {
                    $setting_service = new SettingService();
                    $setting_service->set('part_delete_flow_id', $data['flow_id']);
                    NotificationUtil::make(true, '流程配置成功');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }

    /**
     * 设置资产编号生成配置.
     *
     * @return Action
     */
    public static function setAssetNumberRule(): Action
    {
        return Action::make('资产编号配置')
            ->form([
                Select::make('asset_number_rule_id')
                    ->label('规则')
                    ->options(AssetNumberRuleService::pluckOptions())
                    ->required()
                    ->default(AssetNumberRuleService::getAutoRule(Part::class)?->getAttribute('id')),
                Checkbox::make('is_auto')
                    ->label('自动生成')
                    ->default(AssetNumberRuleService::getAutoRule(Part::class)?->getAttribute('is_auto'))
            ])
            ->action(function (array $data) {
                $data['class_name'] = Part::class;
                AssetNumberRuleService::setAutoRule($data);
                NotificationUtil::make(true, '已选择规则');
            });
    }

    /**
     * 重置资产编号生成配置.
     *
     * @return Action
     */
    public static function resetAssetNumberRule(): Action
    {
        return Action::make('重置资产编号配置')
            ->requiresConfirmation()
            ->action(function () {
                AssetNumberRuleService::resetAutoRule(Part::class);
                NotificationUtil::make(true, '已清除所有规则绑定关系');
            });
    }

    /**
     * 发起配件报废流程表单.
     *
     * @return Action
     */
    public static function createFlowHasFormForDeletingPart(): Action
    {
        return Action::make('报废（流程）')
            ->form([
                TextInput::make('comment')
                    ->label('说明')
                    ->required(),
            ])
            ->action(function (array $data, Part $part) {
                try {
                    $part_delete_flow = $part->service()->getDeleteFlow();
                    $flow_service = new FlowService($part_delete_flow);
                    $asset_number = $part->getAttribute('asset_number');
                    $flow_service->createHasForm(
                        '配件报废单',
                        $asset_number . ' 报废处理',
                        $asset_number
                    );
                    NotificationUtil::make(true, '已创建表单');
                } catch (Exception $exception) {
                    LogUtil::error($exception);
                    NotificationUtil::make(false, $exception);
                }
            });
    }
}