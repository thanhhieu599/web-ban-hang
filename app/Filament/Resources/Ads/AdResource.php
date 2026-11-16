<?php

namespace App\Filament\Resources\Ads;

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

class AdResource extends Resource
{
    protected static ?string $model = Models\Ad::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'quảng cáo';

    protected static ?string $pluralModelLabel = 'Quảng cáo (banner)';

    protected static ?string $navigationLabel = 'Quảng cáo (banner)';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Tên quản lý nội bộ')->required(),
            Select::make('position')->label('Vị trí hiển thị')->options(Models\Ad::POSITIONS)->required(),
            TextInput::make('eyebrow')->label('Dòng chữ nhỏ phía trên')->placeholder('Black Friday · 27.11.2026'),
            TextInput::make('title')->label('Tiêu đề lớn')->placeholder('Black Friday'),
            TextInput::make('subtitle')->label('Tiêu đề phụ')->placeholder('Siêu sale cuối năm'),
            TextInput::make('badge')->label('Nhãn giá / ưu đãi')->placeholder('Giá chỉ từ 5.199.000đ'),
            Select::make('theme')->label('Màu nền')->options(Models\Ad::THEMES)->default('red')->required(),
            Select::make('art')->label('Hình minh họa')->options(art_options())->searchable(),
            FileUpload::make('image')->label('Ảnh banner thiết kế sẵn')->image()->disk('public')->directory('ads')
                ->helperText('Nếu tải ảnh lên, ảnh sẽ thay toàn bộ phần chữ và hình minh họa.')->columnSpanFull(),
            TextInput::make('link')->label('Liên kết khi bấm')->placeholder('/danh-muc/dieu-hoa')->columnSpanFull(),
            TextInput::make('sort')->label('Thứ tự')->numeric()->default(0),
            Toggle::make('is_active')->label('Đang bật')->default(true)->inline(false),
            DateTimePicker::make('starts_at')->label('Bắt đầu hiển thị'),
            DateTimePicker::make('ends_at')->label('Ngừng hiển thị'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên')->searchable()->wrap(),
                TextColumn::make('position')->label('Vị trí')->badge()->color('gray')->formatStateUsing(fn ($state) => Models\Ad::POSITIONS[$state] ?? $state),
                TextColumn::make('title')->label('Tiêu đề')->limit(40),
                TextColumn::make('clicks')->label('Lượt bấm')->sortable(),
                TextColumn::make('ends_at')->label('Ngừng')->dateTime('d/m/Y')->placeholder('Không giới hạn'),
                ToggleColumn::make('is_active')->label('Bật'),
                TextColumn::make('sort')->label('Thứ tự')->sortable(),
            ])
            ->defaultSort('sort')
            ->filters([SelectFilter::make('position')->label('Vị trí')->options(Models\Ad::POSITIONS)])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAds::route('/'),
        ];
    }
}
