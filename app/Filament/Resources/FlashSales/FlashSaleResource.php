<?php

namespace App\Filament\Resources\FlashSales;

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

class FlashSaleResource extends Resource
{
    protected static ?string $model = Models\FlashSale::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'flash sale';

    protected static ?string $pluralModelLabel = 'Flash sale';

    protected static ?string $navigationLabel = 'Flash sale';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Tên chiến dịch')->placeholder('Giá sốc mỗi ngày')->required(),
            TextInput::make('subtitle')->label('Dòng chữ nhỏ')->placeholder('Black Friday · 27.11'),
            DateTimePicker::make('starts_at')->label('Bắt đầu')->default(now()),
            DateTimePicker::make('ends_at')->label('Kết thúc (đồng hồ đếm ngược)')->required()->after('starts_at'),
            Toggle::make('is_active')->label('Đang bật')->default(true)->inline(false),
            Repeater::make('items')->label('Sản phẩm tham gia')->relationship()->columnSpanFull()->columns(12)->schema([
                Select::make('product_id')->label('Sản phẩm')->options(fn () => Models\Product::orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->name.' ('.vnd($p->price).')']))
                    ->searchable()->required()->columnSpan(6),
                TextInput::make('price')->label('Giá flash sale')->numeric()->required()->suffix('đ')->columnSpan(2),
                TextInput::make('quantity')->label('Số suất')->numeric()->default(10)->required()->columnSpan(2),
                TextInput::make('sold')->label('Đã bán')->numeric()->default(0)->columnSpan(2),
            ])->addActionLabel('Thêm sản phẩm')->defaultItems(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Chiến dịch')->searchable(),
                TextColumn::make('items_count')->label('Sản phẩm')->counts('items'),
                TextColumn::make('starts_at')->label('Bắt đầu')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('ends_at')->label('Kết thúc')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('state')->label('Tình trạng')->badge()
                    ->state(fn ($record) => ! $record->is_active ? 'Đã tắt' : ($record->ends_at?->isPast() ? 'Đã kết thúc' : ($record->starts_at?->isFuture() ? 'Sắp diễn ra' : 'Đang chạy')))
                    ->color(fn ($state) => ['Đang chạy' => 'success', 'Sắp diễn ra' => 'info'][$state] ?? 'gray'),
                ToggleColumn::make('is_active')->label('Bật'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlashSales::route('/'),
            'create' => Pages\CreateFlashSale::route('/create'),
            'edit' => Pages\EditFlashSale::route('/{record}/edit'),
        ];
    }
}
