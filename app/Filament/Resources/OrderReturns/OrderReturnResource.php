<?php

namespace App\Filament\Resources\OrderReturns;

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

class OrderReturnResource extends Resource
{
    protected static ?string $model = Models\OrderReturn::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Bán hàng';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'yêu cầu trả hàng';

    protected static ?string $pluralModelLabel = 'Trả hàng';

    protected static ?string $navigationLabel = 'Trả hàng';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $slug = 'returns';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('order_id')->label('Đơn hàng')->relationship('order', 'code')->searchable()->preload()->required(),
            Select::make('status')->label('Trạng thái')->options(Models\OrderReturn::STATUSES)->default('requested')->required(),
            Textarea::make('reason')->label('Lý do trả hàng')->required()->columnSpanFull(),
            TextInput::make('refund_amount')->label('Số tiền hoàn')->numeric()->default(0)->suffix('đ'),
            Textarea::make('admin_note')->label('Ghi chú xử lý')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.code')->label('Đơn hàng')->searchable()->weight('bold'),
                TextColumn::make('order.customer_name')->label('Khách hàng'),
                TextColumn::make('reason')->label('Lý do')->limit(60)->wrap(),
                TextColumn::make('refund_amount')->label('Hoàn tiền')->formatStateUsing(fn ($state) => vnd($state)),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Models\OrderReturn::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['requested' => 'warning', 'approved' => 'info', 'rejected' => 'gray', 'refunded' => 'success'][$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Ngày gửi')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([SelectFilter::make('status')->label('Trạng thái')->options(Models\OrderReturn::STATUSES)])
            ->recordActions([
                Action::make('approve')->label('Chấp nhận')->icon('heroicon-o-check')->color('info')
                    ->visible(fn ($record) => $record->status === 'requested')
                    ->action(fn ($record) => $record->update(['status' => 'approved', 'refund_amount' => $record->refund_amount ?: $record->order->total])),
                Action::make('reject')->label('Từ chối')->icon('heroicon-o-x-mark')->color('gray')
                    ->visible(fn ($record) => $record->status === 'requested')
                    ->schema([Textarea::make('admin_note')->label('Lý do từ chối')->required()])
                    ->action(fn ($record, array $data) => $record->update(['status' => 'rejected'] + $data)),
                Action::make('refund')->label('Đã nhận hàng & hoàn tiền')->icon('heroicon-o-banknotes')->color('success')
                    ->visible(fn ($record) => $record->status === 'approved')->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'refunded']);
                        $record->order->changeStatus('returned');
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrderReturns::route('/'),
        ];
    }
}
