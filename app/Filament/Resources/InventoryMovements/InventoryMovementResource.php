<?php

namespace App\Filament\Resources\InventoryMovements;

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

class InventoryMovementResource extends Resource
{
    protected static ?string $model = Models\InventoryMovement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = 'phiếu kho';

    protected static ?string $pluralModelLabel = 'Nhập xuất kho';

    protected static ?string $navigationLabel = 'Nhập xuất kho';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $slug = 'inventory';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_id')->label('Sản phẩm')->options(fn () => Models\Product::orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => $p->name.' (tồn: '.$p->stock.')']))->searchable()->required()->columnSpanFull(),
            Select::make('type')->label('Loại phiếu')->options(['in' => 'Nhập kho', 'out' => 'Xuất kho'])->default('in')->required(),
            TextInput::make('quantity')->label('Số lượng')->numeric()->minValue(1)->required(),
            TextInput::make('note')->label('Ghi chú')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Thời gian')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('product.name')->label('Sản phẩm')->searchable()->wrap(),
                TextColumn::make('type')->label('Loại')->badge()->formatStateUsing(fn ($state) => Models\InventoryMovement::TYPES[$state] ?? $state)
                    ->color(fn ($state) => ['in' => 'success', 'return' => 'success', 'out' => 'danger', 'order' => 'info'][$state] ?? 'gray'),
                TextColumn::make('quantity')->label('Số lượng')->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '').$state)->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('stock_after')->label('Tồn sau'),
                TextColumn::make('note')->label('Ghi chú')->wrap()->toggleable(),
                TextColumn::make('user.name')->label('Người thực hiện')->placeholder('Hệ thống')->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([SelectFilter::make('type')->label('Loại')->options(Models\InventoryMovement::TYPES)]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInventoryMovements::route('/'),
        ];
    }
}
