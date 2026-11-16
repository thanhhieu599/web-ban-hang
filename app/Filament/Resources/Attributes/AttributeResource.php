<?php

namespace App\Filament\Resources\Attributes;

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

class AttributeResource extends Resource
{
    protected static ?string $model = Models\Attribute::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|\UnitEnum|null $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'thuộc tính';

    protected static ?string $pluralModelLabel = 'Thuộc tính';

    protected static ?string $navigationLabel = 'Thuộc tính';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Tên thuộc tính')->placeholder('Ví dụ: Công suất, Dung tích, Màu sắc')->required()->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
            TextInput::make('slug')->label('Đường dẫn (slug)')->required()->unique(ignoreRecord: true)->helperText('Phần cuối của địa chỉ trang, viết liền không dấu.'),
            Toggle::make('is_filterable')->label('Dùng làm bộ lọc ở trang danh mục')->default(true)->inline(false),
            TextInput::make('sort')->label('Thứ tự')->numeric()->default(0),
            Repeater::make('values')->label('Giá trị')->relationship()->columnSpanFull()->columns(3)->schema([
                TextInput::make('value')->label('Giá trị')->required()->columnSpan(2),
                ColorPicker::make('color')->label('Màu (nếu là thuộc tính màu)'),
            ])->orderColumn('sort')->addActionLabel('Thêm giá trị')->defaultItems(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Thuộc tính')->searchable(),
                TextColumn::make('values.value')->label('Giá trị')->badge()->color('gray')->limitList(6),
                IconColumn::make('is_filterable')->label('Bộ lọc')->boolean(),
                TextColumn::make('sort')->label('Thứ tự')->sortable(),
            ])
            ->defaultSort('sort')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAttributes::route('/'),
        ];
    }
}
