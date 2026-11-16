<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStock extends TableWidget
{
    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table->heading('Sắp hết hàng')
            ->query(Product::query()->whereColumn('stock', '<=', 'low_stock_threshold')->orderBy('stock')->limit(6))
            ->paginated(false)
            ->emptyStateHeading('Tồn kho đang ổn')
            ->columns([
                TextColumn::make('name')->label('Sản phẩm')->wrap()->limit(48),
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('stock')->label('Tồn')->badge()->color(fn ($state) => $state <= 0 ? 'danger' : 'warning'),
            ]);
    }
}
