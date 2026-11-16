<?php

namespace App\Filament\Resources\Collections;

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

class CollectionResource extends Resource
{
    protected static ?string $model = Models\Collection::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'bộ sưu tập';

    protected static ?string $pluralModelLabel = 'Bộ sưu tập';

    protected static ?string $navigationLabel = 'Bộ sưu tập';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Tên bộ sưu tập')->required()->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
            TextInput::make('slug')->label('Đường dẫn (slug)')->required()->unique(ignoreRecord: true)->helperText('Phần cuối của địa chỉ trang, viết liền không dấu.'),
            TextInput::make('subtitle')->label('Dòng chữ nhỏ phía trên')->placeholder('Mùa đông 2026'),
            TextInput::make('sort')->label('Thứ tự')->numeric()->default(0),
            Toggle::make('show_on_home')->label('Hiện trên trang chủ')->inline(false),
            Toggle::make('is_active')->label('Hiển thị')->default(true)->inline(false),
            Select::make('products')->label('Sản phẩm')->relationship('products', 'name')->multiple()->searchable()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Bộ sưu tập')->searchable(),
                TextColumn::make('products_count')->label('Số sản phẩm')->counts('products'),
                ToggleColumn::make('show_on_home')->label('Trang chủ'),
                ToggleColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('sort')->label('Thứ tự')->sortable(),
            ])
            ->defaultSort('sort')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCollections::route('/'),
        ];
    }
}
