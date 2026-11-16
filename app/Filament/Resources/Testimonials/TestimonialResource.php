<?php

namespace App\Filament\Resources\Testimonials;

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

class TestimonialResource extends Resource
{
    protected static ?string $model = Models\Testimonial::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'phản hồi';

    protected static ?string $pluralModelLabel = 'Phản hồi khách hàng';

    protected static ?string $navigationLabel = 'Phản hồi khách hàng';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Tên khách hàng')->required(),
            TextInput::make('role')->label('Giới thiệu ngắn')->placeholder('Khách mua máy giặt, Hà Nội'),
            Textarea::make('content')->label('Nội dung')->required()->rows(3)->columnSpanFull(),
            Select::make('rating')->label('Số sao')->options([5 => 5, 4 => 4, 3 => 3])->default(5),
            FileUpload::make('avatar')->label('Ảnh đại diện')->image()->avatar()->disk('public')->directory('testimonials'),
            TextInput::make('sort')->label('Thứ tự')->numeric()->default(0),
            Toggle::make('is_active')->label('Hiển thị')->default(true)->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')->label('Ảnh')->disk('public')->circular(),
                TextColumn::make('name')->label('Khách hàng')->searchable(),
                TextColumn::make('content')->label('Nội dung')->limit(80)->wrap(),
                TextColumn::make('rating')->label('Sao'),
                ToggleColumn::make('is_active')->label('Hiển thị'),
            ])
            ->defaultSort('sort')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTestimonials::route('/'),
        ];
    }
}
