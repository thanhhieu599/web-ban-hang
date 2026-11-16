<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['code' => Models\Order::newCode(), 'status' => 'confirmed'];
    }

    protected function afterCreate(): void
    {
        foreach ($this->record->items()->with('product')->get() as $item) {
            $item->product?->adjustStock(-$item->quantity, 'order', 'Đơn '.$this->record->code);
            $item->product?->increment('sold_count', $item->quantity);
        }
        $this->record->recalculate();
    }
}
