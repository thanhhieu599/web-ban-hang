<?php

namespace App\Filament\Resources\Products;

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

class ProductResource extends Resource
{
    protected static ?string $model = Models\Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Sản phẩm';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'sản phẩm';

    protected static ?string $pluralModelLabel = 'Sản phẩm';

    protected static ?string $navigationLabel = 'Sản phẩm';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([
                Tab::make('Thông tin')->columns(2)->schema([
                    TextInput::make('name')->label('Tên sản phẩm')->required()->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null)->columnSpanFull(),
                    TextInput::make('slug')->label('Đường dẫn (slug)')->required()->unique(ignoreRecord: true)->helperText('Phần cuối của địa chỉ trang, viết liền không dấu.'),
                    TextInput::make('sku')->label('Mã SKU')->required()->unique(ignoreRecord: true)->default(fn () => 'SP'.strtoupper(Str::random(6))),
                    Select::make('category_id')->label('Danh mục')->required()->searchable()
                        ->options(fn () => Models\Category::whereNotNull('parent_id')->with('parent')->get()->groupBy('parent.name')->map(fn ($g) => $g->pluck('name', 'id'))->all()),
                    Select::make('brand_id')->label('Thương hiệu')->relationship('brand', 'name')->searchable()->preload(),
                    Textarea::make('short_description')->label('Mô tả ngắn')->rows(2)->maxLength(500)->columnSpanFull(),
                    RichEditor::make('description')->label('Mô tả chi tiết')->columnSpanFull(),
                ]),
                Tab::make('Giá & kho')->columns(3)->schema([
                    TextInput::make('price')->label('Giá bán')->numeric()->required()->suffix('đ'),
                    TextInput::make('compare_price')->label('Giá niêm yết (gạch ngang)')->numeric()->suffix('đ'),
                    TextInput::make('cost')->label('Giá vốn')->numeric()->default(0)->suffix('đ')->helperText('Dùng để tính lợi nhuận trong báo cáo.'),
                    TextInput::make('stock')->label('Tồn kho')->numeric()->default(0)->disabledOn('edit')->helperText('Sau khi tạo, thay đổi tồn kho bằng nút "Kho" để lưu lịch sử.'),
                    TextInput::make('low_stock_threshold')->label('Cảnh báo khi tồn dưới')->numeric()->default(5),
                    TextInput::make('warranty')->label('Bảo hành')->placeholder('24 tháng chính hãng'),
                ]),
                Tab::make('Hình ảnh')->schema([
                    FileUpload::make('images')->label('Ảnh sản phẩm')->image()->multiple()->reorderable()->disk('public')->directory('products')->maxFiles(8)->imageEditor(),
                    Select::make('art')->label('Hình minh họa (dùng khi chưa có ảnh)')->options(art_options())->searchable(),
                ]),
                Tab::make('Phân loại')->columns(2)->schema([
                    Select::make('attributeValues')->label('Thuộc tính')->relationship('attributeValues', 'value')->multiple()->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->label),
                    Select::make('options')->label('Tùy chọn mua kèm')->relationship('options', 'name')->multiple()->preload(),
                    Select::make('collections')->label('Bộ sưu tập')->relationship('collections', 'name')->multiple()->preload(),
                    Select::make('labels')->label('Nhãn')->relationship('labels', 'name')->multiple()->preload(),
                ]),
                Tab::make('Thông số')->schema([
                    KeyValue::make('specs')->label('Thông số kỹ thuật')->keyLabel('Tên thông số')->valueLabel('Giá trị')->addActionLabel('Thêm thông số'),
                ]),
                Tab::make('Hiển thị & SEO')->columns(2)->schema([
                    Select::make('status')->label('Trạng thái')->options(Models\Product::STATUSES)->default('published')->required(),
                    Toggle::make('is_featured')->label('Sản phẩm nổi bật')->inline(false),
                    TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(70),
                    TextInput::make('meta_description')->label('Mô tả SEO')->maxLength(170),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')->label('Ảnh')->disk('public')->limit(1)->square(),
                TextColumn::make('name')->label('Tên sản phẩm')->searchable()->sortable()->wrap()->description(fn ($record) => $record->sku),
                TextColumn::make('category.name')->label('Danh mục')->sortable()->toggleable(),
                TextColumn::make('brand.name')->label('Thương hiệu')->toggleable(),
                TextColumn::make('price')->label('Giá bán')->sortable()->formatStateUsing(fn ($state) => vnd($state)),
                TextColumn::make('stock')->label('Tồn kho')->sortable()->badge()
                    ->color(fn ($record) => $record->stock <= 0 ? 'danger' : ($record->is_low_stock ? 'warning' : 'success')),
                TextColumn::make('sold_count')->label('Đã bán')->sortable()->toggleable(),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Models\Product::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['draft' => 'gray', 'pending' => 'warning', 'published' => 'success'][$state] ?? 'gray'),
                IconColumn::make('is_featured')->label('Nổi bật')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('category_id')->label('Danh mục')->relationship('category', 'name')->searchable()->preload(),
                SelectFilter::make('brand_id')->label('Thương hiệu')->relationship('brand', 'name'),
                SelectFilter::make('status')->label('Trạng thái')->options(Models\Product::STATUSES),
                Filter::make('low_stock')->label('Sắp hết hàng')->query(fn (Builder $q) => $q->whereColumn('stock', '<=', 'low_stock_threshold')),
            ])
            ->recordActions([
                Action::make('approve')->label('Duyệt')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(fn ($record) => $record->update(['status' => 'published'])),
                Action::make('stock')->label('Kho')->icon('heroicon-o-archive-box')->color('gray')
                    ->schema([
                        Select::make('type')->label('Loại')->options(['in' => 'Nhập kho', 'out' => 'Xuất kho'])->default('in')->required(),
                        TextInput::make('quantity')->label('Số lượng')->numeric()->minValue(1)->required(),
                        TextInput::make('note')->label('Ghi chú'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->adjustStock($data['type'] === 'in' ? (int) $data['quantity'] : -(int) $data['quantity'], $data['type'], $data['note'] ?? null);
                        Notification::make()->title('Đã cập nhật tồn kho: '.$record->stock)->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('publish')->label('Duyệt & đăng bán')->icon('heroicon-o-check-badge')
                    ->action(fn ($records) => $records->each->update(['status' => 'published']))->deselectRecordsAfterCompletion(),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
