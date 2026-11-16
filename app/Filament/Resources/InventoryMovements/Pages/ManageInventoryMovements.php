<?php

namespace App\Filament\Resources\InventoryMovements\Pages;

use App\Filament\Resources\InventoryMovements\InventoryMovementResource;
use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;

class ManageInventoryMovements extends ManageRecords
{
    protected static string $resource = InventoryMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tạo phiếu nhập/xuất')->using(function (array $data) {
            $product = Models\Product::findOrFail($data['product_id']);
            $product->adjustStock($data['type'] === 'in' ? (int) $data['quantity'] : -(int) $data['quantity'], $data['type'], $data['note'] ?? null);

            return $product->inventoryMovements()->latest('id')->first();
        })];
    }
}
