<?php

namespace App\Filament\Resources\Options\Pages;

use App\Filament\Resources\Options\OptionResource;
use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;

class ManageOptions extends ManageRecords
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
