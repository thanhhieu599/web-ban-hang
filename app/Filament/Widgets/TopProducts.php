<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopProducts extends TableWidget
{
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        return $table->heading('Sản phẩm bán chạy')
            ->query(Product::query()->where('sold_count', '>', 0)->orderByDesc('sold_count')->limit(6))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label('Sản phẩm')->wrap()->limit(48),
                TextColumn::make('sold_count')->label('Đã bán')->badge()->color('success'),
                TextColumn::make('price')->label('Giá')->formatStateUsing(fn ($state) => vnd($state)),
            ]);
    }
}
