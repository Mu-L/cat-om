<?php

namespace App\Filament\Imports;

use App\Models\Vendor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class VendorImporter extends Importer
{
    protected static ?string $model = Vendor::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('名称')
                ->requiredMapping()
                ->example('张三三'),
            ImportColumn::make('address')
                ->label('地址')
                ->requiredMapping()
                ->example('北京市朝阳区'),
            ImportColumn::make('public_phone_number')
                ->label('对公电话')
                ->requiredMapping()
                ->example('010-12345678'),
            ImportColumn::make('referrer')
                ->label('引荐人')
                ->requiredMapping()
                ->example('李四四'),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = '你的厂商导入已完成并有 '.number_format($import->successful_rows).' 行记录被导入。';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).'行导入失败。';
        }

        return $body;
    }

    public function resolveRecord(): ?Vendor
    {
        // return Device::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Vendor();
    }
}
