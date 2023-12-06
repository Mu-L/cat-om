<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Actions\DeviceAction;
use App\Filament\Resources\DeviceResource;
use App\Models\Device;
use App\Models\DeviceHasUser;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HasUser extends ManageRelatedRecords
{
    protected static string $resource = DeviceResource::class;

    protected static string $relationship = 'hasUsers';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $title = '用户';

    public static function getNavigationLabel(): string
    {
        return '用户';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->badge()
                    ->color(function (DeviceHasUser $device_user_track) {
                        if ($device_user_track->getAttribute('deleted_at')) {
                            return 'danger';
                        } else {
                            return 'success';
                        }
                    })
                    ->searchable()
                    ->label('管理者'),
                Tables\Columns\TextColumn::make('comment')
                    ->label('分配说明'),
                Tables\Columns\TextColumn::make('delete_comment')
                    ->label('解除分配说明'),
                Tables\Columns\TextColumn::make('created_at')
                    ->size(TextColumnSize::ExtraSmall)
                    ->label('分配时间'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->size(TextColumnSize::ExtraSmall)
                    ->label('解除分配时间'),
            ])
            ->defaultSort('deleted_at', 'desc')
            ->filters([

            ])
            ->headerActions([
                // 分配管理者
                DeviceAction::createDeviceHasUser($this->getOwnerRecord())
                    ->visible(function () {
                        /* @var Device $device */
                        $device = $this->getOwnerRecord();
                        $can = auth()->user()->can('assign_user_device');

                        return $can && !$device->service()->isExistHasUser();
                    }),
                // 解除管理者
                DeviceAction::deleteDeviceHasUser($this->getOwnerRecord())
                    ->visible(function () {
                        /* @var Device $device */
                        $device = $this->getOwnerRecord();
                        $can = auth()->user()->can('delete_assign_user_device');

                        return $can && $device->service()->isExistHasUser();
                    }),
            ])
            ->actions([

            ])
            ->bulkActions([

            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->orderByDesc('created_at')
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}