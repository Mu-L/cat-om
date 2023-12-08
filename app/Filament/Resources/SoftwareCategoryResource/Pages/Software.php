<?php

namespace App\Filament\Resources\SoftwareCategoryResource\Pages;

use App\Filament\Actions\SoftwareCategoryAction;
use App\Filament\Resources\SoftwareCategoryResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Software extends ManageRelatedRecords
{
    protected static string $resource = SoftwareCategoryResource::class;

    protected static string $relationship = 'software';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $title = '软件';

    public static function getNavigationLabel(): string
    {
        return '软件';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('asset_number')
            ->columns([
                Tables\Columns\TextColumn::make('asset_number')
                    ->searchable()
                    ->toggleable()
                    ->label('资产编号'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([

            ])
            ->actions([
                // 前往软件清单
                SoftwareCategoryAction::toSoftware(),
            ])
            ->bulkActions([

            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}