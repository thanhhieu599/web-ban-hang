<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table->heading('Đơn hàng mới nhất')
            ->query(Order::query()->latest('id')->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('code')->label('Mã đơn')->weight('bold'),
                TextColumn::make('customer_name')->label('Khách hàng'),
                TextColumn::make('total')->label('Tổng tiền')->formatStateUsing(fn ($state) => vnd($state)),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Order::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Ngày đặt')->since(),
            ])
            ->recordUrl(fn (Order $record) => OrderResource::getUrl('edit', ['record' => $record]));
    }
}
