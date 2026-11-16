<?php

namespace App\Filament\Resources\FlashSales\Pages;

use App\Filament\Resources\FlashSales\FlashSaleResource;
use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\EditRecord;

class EditFlashSale extends EditRecord
{
    protected static string $resource = FlashSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
