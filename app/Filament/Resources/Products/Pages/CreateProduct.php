<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        if ($this->record->stock) {
            $this->record->inventoryMovements()->create([
                'user_id' => auth()->id(), 'type' => 'in', 'quantity' => $this->record->stock,
                'stock_after' => $this->record->stock, 'note' => 'Tồn kho ban đầu khi tạo sản phẩm',
            ]);
        }
    }
}
