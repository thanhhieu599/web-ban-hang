<?php

namespace App\Filament\Resources\Ads\Pages;

use App\Filament\Resources\Ads\AdResource;
use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;

class ManageAds extends ManageRecords
{
    protected static string $resource = AdResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
