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
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('export')->label('Xuất Excel (CSV)')->icon('heroicon-o-arrow-down-tray')->color('gray')->url(route('admin.export', 'don-hang'))->openUrlInNewTab(), CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả'),
            'new' => Tab::make('Đơn mới')->badge(Models\Order::where('status', 'pending')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'open' => Tab::make('Chưa hoàn thành')->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['confirmed', 'shipping'])),
            'done' => Tab::make('Hoàn thành')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
            'returned' => Tab::make('Trả lại / Hủy')->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['returned', 'cancelled'])),
        ];
    }
}
