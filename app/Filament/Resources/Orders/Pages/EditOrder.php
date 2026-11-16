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
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make(OrderResource::statusActions())->label('Xử lý đơn')->icon('heroicon-o-bolt')->button(),
            DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Trạng thái: '.$this->record->status_label.' · Tổng tiền: '.vnd($this->record->total);
    }

    protected function afterSave(): void
    {
        $this->record->recalculate();
    }
}
