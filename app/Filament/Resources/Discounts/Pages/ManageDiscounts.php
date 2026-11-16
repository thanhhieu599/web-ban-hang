<?php

namespace App\Filament\Resources\Discounts\Pages;

use App\Filament\Resources\Discounts\DiscountResource;
use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;

class ManageDiscounts extends ManageRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
