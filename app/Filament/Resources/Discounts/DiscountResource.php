<?php

namespace App\Filament\Resources\Discounts;

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

class DiscountResource extends Resource
{
    protected static ?string $model = Models\Discount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'mã giảm giá';

    protected static ?string $pluralModelLabel = 'Mã giảm giá';

    protected static ?string $navigationLabel = 'Mã giảm giá';

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Mã')->required()->unique(ignoreRecord: true)->maxLength(40)
                ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))->helperText('Khách nhập mã này ở giỏ hàng.'),
            TextInput::make('name')->label('Tên chương trình')->required(),
            Select::make('type')->label('Kiểu giảm')->options(['percent' => 'Theo phần trăm (%)', 'fixed' => 'Số tiền cố định (đ)'])->default('percent')->required()->live(),
            TextInput::make('value')->label('Giá trị')->numeric()->required()->suffix(fn (Get $get) => $get('type') === 'fixed' ? 'đ' : '%'),
            TextInput::make('min_order')->label('Đơn tối thiểu')->numeric()->default(0)->suffix('đ'),
            TextInput::make('max_discount')->label('Giảm tối đa')->numeric()->suffix('đ')->helperText('Để trống nếu không giới hạn.'),
            TextInput::make('usage_limit')->label('Số lượt dùng tối đa')->numeric()->helperText('Để trống nếu không giới hạn.'),
            Toggle::make('is_active')->label('Đang bật')->default(true)->inline(false),
            DateTimePicker::make('starts_at')->label('Bắt đầu'),
            DateTimePicker::make('ends_at')->label('Kết thúc'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Mã')->searchable()->weight('bold')->copyable(),
                TextColumn::make('name')->label('Chương trình')->searchable()->wrap(),
                TextColumn::make('value')->label('Giảm')->formatStateUsing(fn ($record) => $record->type === 'percent' ? $record->value.'%' : vnd($record->value)),
                TextColumn::make('min_order')->label('Đơn tối thiểu')->formatStateUsing(fn ($state) => vnd($state)),
                TextColumn::make('used')->label('Đã dùng')->formatStateUsing(fn ($record) => $record->used.($record->usage_limit ? ' / '.$record->usage_limit : '')),
                TextColumn::make('ends_at')->label('Hết hạn')->dateTime('d/m/Y')->placeholder('Không giới hạn')->sortable(),
                ToggleColumn::make('is_active')->label('Bật'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDiscounts::route('/'),
        ];
    }
}
