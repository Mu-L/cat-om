<?php

namespace App\Filament\Forms;

use Awcodes\Shout\Components\Shout;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class AssetNumberRuleForm
{
    /**
     * 创建或编辑.
     */
    public static function createOrEdit(): array
    {
        return [
            TextInput::make('name')
                ->label(__('cat/asset_number_rule.name'))
                ->required(),
            Textarea::make('formula')
                ->label(__('cat/asset_number_rule.formula'))
                ->required(),
            TextInput::make('auto_increment_length')
                ->label(__('cat/asset_number_rule.auto_increment_length'))
                ->numeric()
                ->required(),
            Shout::make('description')
                ->label(__('cat/asset_number_rule.description'))
                ->content(__('cat/asset_number_rule.form.description_create_helper')),
        ];
    }
}
