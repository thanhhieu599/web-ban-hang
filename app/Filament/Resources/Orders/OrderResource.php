<?php

namespace App\Filament\Resources\Orders;

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

class OrderResource extends Resource
{
    protected static ?string $model = Models\Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Bán hàng';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'đơn hàng';

    protected static ?string $pluralModelLabel = 'Đơn hàng';

    protected static ?string $navigationLabel = 'Đơn hàng';

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Khách hàng')->columns(2)->schema([
                TextInput::make('customer_name')->label('Họ tên')->required(),
                TextInput::make('phone')->label('Số điện thoại')->tel()->required(),
                TextInput::make('email')->label('Email')->email(),
                TextInput::make('province')->label('Tỉnh / Thành phố'),
                TextInput::make('address')->label('Địa chỉ nhận hàng')->required()->columnSpanFull(),
                Textarea::make('note')->label('Ghi chú của khách')->rows(2)->columnSpanFull(),
            ]),
            Section::make('Thanh toán')->columns(2)->schema([
                Select::make('payment_method')->label('Hình thức')->options(Models\Order::PAYMENT_METHODS)->default('cod')->required(),
                Select::make('payment_status')->label('Tình trạng')->options(Models\Order::PAYMENT_STATUSES)->default('unpaid')->required(),
                TextInput::make('shipping_fee')->label('Phí giao hàng')->numeric()->default(0)->suffix('đ'),
                TextInput::make('discount_total')->label('Giảm giá')->numeric()->default(0)->suffix('đ'),
                Textarea::make('admin_note')->label('Ghi chú nội bộ')->rows(2)->columnSpanFull(),
            ]),
            Section::make('Sản phẩm trong đơn')->description('Tổng tiền được tính lại tự động khi lưu.')->columnSpanFull()->schema([
                Repeater::make('items')->hiddenLabel()->relationship()->columns(12)->schema([
                    Select::make('product_id')->label('Sản phẩm')->options(fn () => Models\Product::orderBy('name')->pluck('name', 'id'))->searchable()->required()->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if ($p = Models\Product::find($state)) {
                                $set('name', $p->name);
                                $set('sku', $p->sku);
                                $set('price', $p->final_price);
                                $set('cost', $p->cost);
                            }
                        })->columnSpan(5),
                    TextInput::make('name')->label('Tên hiển thị')->required()->columnSpan(3),
                    TextInput::make('price')->label('Đơn giá')->numeric()->required()->columnSpan(2),
                    TextInput::make('quantity')->label('SL')->numeric()->default(1)->minValue(1)->required()->columnSpan(2),
                    Hidden::make('sku'),
                    Hidden::make('cost')->default(0),
                ])->addActionLabel('Thêm sản phẩm')->defaultItems(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Mã đơn')->searchable()->sortable()->weight('bold')->copyable(),
                TextColumn::make('customer_name')->label('Khách hàng')->searchable()->description(fn ($record) => $record->phone),
                TextColumn::make('phone')->label('SĐT')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')->label('SP')->counts('items'),
                TextColumn::make('total')->label('Tổng tiền')->sortable()->formatStateUsing(fn ($state) => vnd($state)),
                TextColumn::make('payment_status')->label('Thanh toán')->badge()->formatStateUsing(fn ($state) => Models\Order::PAYMENT_STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['unpaid' => 'gray', 'paid' => 'success', 'refunded' => 'danger'][$state] ?? 'gray'),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Models\Order::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Models\Order::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Ngày đặt')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Trạng thái')->options(Models\Order::STATUSES),
                SelectFilter::make('payment_status')->label('Thanh toán')->options(Models\Order::PAYMENT_STATUSES),
                Filter::make('created_at')->schema([DatePicker::make('from')->label('Từ ngày'), DatePicker::make('until')->label('Đến ngày')])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([
                ActionGroup::make(static::statusActions())->label('Xử lý')->icon('heroicon-o-bolt')->button()->size('sm'),
                EditAction::make()->label('Chi tiết'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    /** Các thao tác xử lý đơn, dùng chung cho bảng danh sách và trang chi tiết. */
    public static function statusActions(): array
    {
        return [
            Action::make('confirm')->label('Xác nhận đơn')->icon('heroicon-o-check')->color('info')
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(fn ($record) => $record->changeStatus('confirmed')),
            Action::make('ship')->label('Tạo vận đơn & giao hàng')->icon('heroicon-o-truck')->color('primary')
                ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                ->schema([
                    Select::make('carrier')->label('Đơn vị vận chuyển')->options(array_combine(Models\Shipment::CARRIERS, Models\Shipment::CARRIERS))->default('Giao hàng nội bộ')->required(),
                    TextInput::make('tracking_code')->label('Mã vận đơn'),
                    TextInput::make('fee')->label('Phí trả cho đơn vị vận chuyển')->numeric()->default(0)->suffix('đ'),
                ])
                ->action(fn ($record, array $data) => $record->shipments()->create($data + ['status' => 'in_transit', 'shipped_at' => now()])),
            Action::make('complete')->label('Hoàn thành')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn ($record) => in_array($record->status, ['confirmed', 'shipping']))
                ->requiresConfirmation()->action(fn ($record) => $record->changeStatus('completed')),
            Action::make('invoice')->label('Xuất hóa đơn')->icon('heroicon-o-document-text')->color('gray')
                ->visible(fn ($record) => ! in_array($record->status, ['cancelled']))
                ->schema([
                    TextInput::make('company_name')->label('Tên công ty / người mua')->default(fn ($record) => $record->customer_name),
                    TextInput::make('tax_code')->label('Mã số thuế'),
                    TextInput::make('address')->label('Địa chỉ')->default(fn ($record) => $record->address),
                ])
                ->action(function ($record, array $data) {
                    $invoice = $record->invoices()->create($data + ['number' => Models\Invoice::newNumber(), 'amount' => $record->total, 'issued_at' => now()]);
                    Notification::make()->title('Đã xuất hóa đơn '.$invoice->number)->success()->send();
                }),
            Action::make('print')->label('In đơn hàng')->icon('heroicon-o-printer')->color('gray')
                ->url(fn ($record) => route('admin.orders.print', $record))->openUrlInNewTab(),
            Action::make('cancel')->label('Hủy đơn')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed', 'shipping']))
                ->requiresConfirmation()->modalDescription('Tồn kho của các sản phẩm trong đơn sẽ được cộng lại.')
                ->action(fn ($record) => $record->changeStatus('cancelled')),
            Action::make('return')->label('Nhận hàng trả lại')->icon('heroicon-o-arrow-uturn-left')->color('danger')
                ->visible(fn ($record) => $record->status === 'completed')
                ->requiresConfirmation()->modalDescription('Đơn chuyển sang "Đã trả hàng", hoàn kho và đánh dấu hoàn tiền.')
                ->action(fn ($record) => $record->changeStatus('returned')),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Models\Order::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Đơn chờ xác nhận';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
