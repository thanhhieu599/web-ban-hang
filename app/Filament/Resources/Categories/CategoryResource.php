<?php

namespace App\Filament\Resources\Categories;

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

class CategoryResource extends Resource
{
    protected static ?string $model = Models\Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'danh mục';

    protected static ?string $pluralModelLabel = 'Danh mục';

    protected static ?string $navigationLabel = 'Danh mục';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Tên danh mục')->required()->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
            TextInput::make('slug')->label('Đường dẫn (slug)')->required()->unique(ignoreRecord: true)->helperText('Phần cuối của địa chỉ trang, viết liền không dấu.'),
            Select::make('parent_id')->label('Danh mục cha')->placeholder('Không có (đây là danh mục cấp 1)')
                ->options(fn (?Models\Category $record) => Models\Category::whereNull('parent_id')->when($record, fn ($q) => $q->whereKeyNot($record->id))->pluck('name', 'id'))
                ->helperText('Menu hiển thị 2 cấp: cấp 1 là nhóm, cấp 2 chứa sản phẩm.'),
            Select::make('icon')->label('Hình minh họa')->options(art_options())->searchable(),
            FileUpload::make('image')->label('Ảnh danh mục (tùy chọn)')->image()->disk('public')->directory('categories'),
            TextInput::make('sale_badge')->label('Nhãn giảm giá')->placeholder('-50%')->maxLength(20),
            TextInput::make('sort')->label('Thứ tự')->numeric()->default(0),
            Toggle::make('is_featured')->label('Hiện ở "Danh mục nổi bật" trang chủ')->inline(false),
            Toggle::make('is_active')->label('Hiển thị')->default(true)->inline(false),
            Textarea::make('description')->label('Mô tả')->columnSpanFull(),
            Section::make('SEO')->collapsed()->columnSpanFull()->columns(2)->schema([
                TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(70),
                TextInput::make('meta_description')->label('Mô tả SEO')->maxLength(170),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên')->searchable()->sortable(),
                TextColumn::make('parent.name')->label('Danh mục cha')->placeholder('Cấp 1')->badge()->color('gray'),
                TextColumn::make('products_count')->label('Số sản phẩm')->counts('products')->sortable(),
                TextColumn::make('sale_badge')->label('Nhãn'),
                ToggleColumn::make('is_featured')->label('Nổi bật'),
                ToggleColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('sort')->label('Thứ tự')->sortable(),
            ])
            ->defaultSort('sort')
            ->filters([SelectFilter::make('parent_id')->label('Danh mục cha')->options(fn () => Models\Category::whereNull('parent_id')->pluck('name', 'id'))])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategories::route('/'),
        ];
    }
}
