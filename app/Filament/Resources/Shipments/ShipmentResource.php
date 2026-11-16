<?php

namespace App\Filament\Resources\Shipments;

use App\Models;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ShipmentResource extends Resource
{
    protected static ?string $model = Models\Shipment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Bán hàng';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'vận đơn';

    protected static ?string $pluralModelLabel = 'Vận chuyển';

    protected static ?string $navigationLabel = 'Vận chuyển';

    protected static ?string $recordTitleAttribute = 'tracking_code';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('order_id')->label('Đơn hàng')->relationship('order', 'code')->searchable()->preload()->required(),
            Select::make('carrier')->label('Đơn vị vận chuyển')->options(array_combine(Models\Shipment::CARRIERS, Models\Shipment::CARRIERS))->required(),
            TextInput::make('tracking_code')->label('Mã vận đơn'),
            Select::make('status')->label('Trạng thái')->options(Models\Shipment::STATUSES)->default('pending')->required()
                ->helperText('Khi chọn "Đã giao", đơn hàng tự chuyển sang Hoàn thành.'),
            TextInput::make('fee')->label('Phí vận chuyển')->numeric()->default(0)->suffix('đ'),
            DateTimePicker::make('shipped_at')->label('Ngày gửi'),
            DateTimePicker::make('delivered_at')->label('Ngày giao'),
            TextInput::make('note')->label('Ghi chú')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.code')->label('Đơn hàng')->searchable()->weight('bold'),
                TextColumn::make('order.customer_name')->label('Khách hàng')->searchable(),
                TextColumn::make('carrier')->label('Đơn vị'),
                TextColumn::make('tracking_code')->label('Mã vận đơn')->searchable()->copyable()->placeholder('Chưa có'),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Models\Shipment::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['pending' => 'gray', 'picked' => 'info', 'in_transit' => 'primary', 'delivered' => 'success', 'failed' => 'danger'][$state] ?? 'gray'),
                TextColumn::make('fee')->label('Phí')->formatStateUsing(fn ($state) => vnd($state)),
                TextColumn::make('shipped_at')->label('Ngày gửi')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([SelectFilter::make('status')->label('Trạng thái')->options(Models\Shipment::STATUSES)])
            ->recordActions([
                Action::make('delivered')->label('Đã giao')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn ($record) => ! in_array($record->status, ['delivered', 'failed']))
                    ->action(fn ($record) => $record->update(['status' => 'delivered', 'delivered_at' => now()])),
                EditAction::make(), DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageShipments::route('/'),
        ];
    }
}
