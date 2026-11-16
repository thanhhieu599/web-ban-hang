<?php

namespace App\Filament\Resources\Reviews;

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

class ReviewResource extends Resource
{
    protected static ?string $model = Models\Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'đánh giá';

    protected static ?string $pluralModelLabel = 'Đánh giá';

    protected static ?string $navigationLabel = 'Đánh giá';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_id')->label('Sản phẩm')->relationship('product', 'name')->searchable()->required()->columnSpanFull(),
            TextInput::make('name')->label('Người đánh giá')->required(),
            Select::make('rating')->label('Số sao')->options([5 => '5 sao', 4 => '4 sao', 3 => '3 sao', 2 => '2 sao', 1 => '1 sao'])->default(5)->required(),
            Textarea::make('comment')->label('Nội dung')->rows(3)->columnSpanFull(),
            Select::make('status')->label('Trạng thái')->options(Models\Review::STATUSES)->default('approved')->required(),
            Textarea::make('reply')->label('Phản hồi của cửa hàng')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->label('Sản phẩm')->searchable()->wrap()->limit(50),
                TextColumn::make('name')->label('Khách hàng')->searchable(),
                TextColumn::make('rating')->label('Sao')->formatStateUsing(fn ($state) => str_repeat('★', (int) $state))->color('warning')->sortable(),
                TextColumn::make('comment')->label('Nội dung')->limit(70)->wrap(),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Models\Review::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'gray'][$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Ngày')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([SelectFilter::make('status')->label('Trạng thái')->options(Models\Review::STATUSES), SelectFilter::make('rating')->label('Số sao')->options([5 => 5, 4 => 4, 3 => 3, 2 => 2, 1 => 1])])
            ->recordActions([
                Action::make('approve')->label('Duyệt')->icon('heroicon-o-check')->color('success')
                    ->visible(fn ($record) => $record->status !== 'approved')->action(fn ($record) => $record->update(['status' => 'approved'])),
                Action::make('reject')->label('Ẩn')->icon('heroicon-o-eye-slash')->color('gray')
                    ->visible(fn ($record) => $record->status !== 'rejected')->action(fn ($record) => $record->update(['status' => 'rejected'])),
                EditAction::make()->label('Phản hồi'), DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('approveAll')->label('Duyệt đã chọn')->icon('heroicon-o-check')->action(fn ($records) => $records->each->update(['status' => 'approved']))->deselectRecordsAfterCompletion(),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Models\Review::where('status', 'pending')->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageReviews::route('/'),
        ];
    }
}
