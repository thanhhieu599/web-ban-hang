<?php

namespace App\Filament\Pages;

use App\Services\Report;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Báo cáo kinh doanh';

    protected static ?string $navigationLabel = 'Báo cáo kinh doanh';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('period')->label('Khoảng thời gian')->options(Report::PERIODS)->default('30')->selectablePlaceholder(false),
            ])->columnSpanFull(),
        ]);
    }
}
