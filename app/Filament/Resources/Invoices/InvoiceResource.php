<?php

namespace App\Filament\Resources\Invoices;

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

class InvoiceResource extends Resource
{
    protected static ?string $model = Models\Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Bán hàng';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'hóa đơn';

    protected static ?string $pluralModelLabel = 'Hóa đơn';

    protected static ?string $navigationLabel = 'Hóa đơn';

    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('order_id')->label('Đơn hàng')->relationship('order', 'code')->searchable()->preload()->required()->live()
                ->afterStateUpdated(fn (Set $set, $state) => $set('amount', Models\Order::find($state)?->total)),
            TextInput::make('number')->label('Số hóa đơn')->default(fn () => Models\Invoice::newNumber())->required()->unique(ignoreRecord: true),
            TextInput::make('company_name')->label('Tên công ty / người mua'),
            TextInput::make('tax_code')->label('Mã số thuế'),
            TextInput::make('address')->label('Địa chỉ')->columnSpanFull(),
            TextInput::make('amount')->label('Số tiền')->numeric()->required()->suffix('đ'),
            Select::make('status')->label('Trạng thái')->options(Models\Invoice::STATUSES)->default('issued')->required(),
            DateTimePicker::make('issued_at')->label('Ngày xuất')->default(now()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Số hóa đơn')->searchable()->weight('bold'),
                TextColumn::make('order.code')->label('Đơn hàng')->searchable(),
                TextColumn::make('company_name')->label('Người mua')->searchable()->wrap(),
                TextColumn::make('tax_code')->label('MST')->toggleable(),
                TextColumn::make('amount')->label('Số tiền')->formatStateUsing(fn ($state) => vnd($state))->sortable(),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Models\Invoice::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['issued' => 'info', 'paid' => 'success', 'cancelled' => 'gray'][$state] ?? 'gray'),
                TextColumn::make('issued_at')->label('Ngày xuất')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('print')->label('In')->icon('heroicon-o-printer')->color('gray')
                    ->url(fn ($record) => route('admin.invoices.print', $record))->openUrlInNewTab(),
                EditAction::make(), DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }
}
