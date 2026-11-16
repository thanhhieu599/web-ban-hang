<?php

namespace App\Filament\Resources\Options;

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

class OptionResource extends Resource
{
    protected static ?string $model = Models\Option::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|\UnitEnum|null $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'tùy chọn';

    protected static ?string $pluralModelLabel = 'Tùy chọn';

    protected static ?string $navigationLabel = 'Tùy chọn';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Tên tùy chọn')->placeholder('Ví dụ: Gói bảo hành mở rộng, Dịch vụ lắp đặt')->required(),
            Toggle::make('is_required')->label('Bắt buộc chọn')->inline(false),
            Repeater::make('values')->label('Các lựa chọn')->columnSpanFull()->columns(2)->schema([
                TextInput::make('label')->label('Tên lựa chọn')->required(),
                TextInput::make('price')->label('Cộng thêm vào giá')->numeric()->default(0)->suffix('đ'),
            ])->addActionLabel('Thêm lựa chọn')->defaultItems(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tùy chọn')->searchable(),
                TextColumn::make('values')->label('Lựa chọn')->formatStateUsing(fn ($record) => collect($record->values)->pluck('label')->join(', '))->wrap(),
                IconColumn::make('is_required')->label('Bắt buộc')->boolean(),
                TextColumn::make('products_count')->label('Số sản phẩm')->counts('products'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOptions::route('/'),
        ];
    }
}
