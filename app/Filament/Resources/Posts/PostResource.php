<?php

namespace App\Filament\Resources\Posts;

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

class PostResource extends Resource
{
    protected static ?string $model = Models\Post::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|\UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'bài viết';

    protected static ?string $pluralModelLabel = 'Bài viết';

    protected static ?string $navigationLabel = 'Bài viết';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make()->columnSpan(2)->schema([
                TextInput::make('title')->label('Tiêu đề')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->label('Đường dẫn (slug)')->required()->unique(ignoreRecord: true)->helperText('Phần cuối của địa chỉ trang, viết liền không dấu.'),
                Textarea::make('excerpt')->label('Tóm tắt')->rows(2)->maxLength(500),
                RichEditor::make('content')->label('Nội dung')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('posts'),
            ]),
            Section::make()->columnSpan(1)->schema([
                Select::make('status')->label('Trạng thái')->options(['draft' => 'Bản nháp', 'published' => 'Đã đăng'])->default('published')->required(),
                DateTimePicker::make('published_at')->label('Ngày đăng')->default(now()),
                Select::make('post_category_id')->label('Chuyên mục')->relationship('category', 'name')->preload()
                    ->createOptionForm([TextInput::make('name')->label('Tên')->required(), TextInput::make('slug')->required()]),
                Select::make('tags')->label('Thẻ (tags)')->relationship('tags', 'name')->multiple()->preload()
                    ->createOptionForm([TextInput::make('name')->label('Tên')->required(), TextInput::make('slug')->required()]),
                Toggle::make('is_featured')->label('Bài nổi bật'),
                FileUpload::make('image')->label('Ảnh đại diện')->image()->imageEditor()->disk('public')->directory('posts')->maxSize(3072)->helperText('Ảnh ngang 1200×675 px (16:9).'),
                Select::make('theme')->label('Màu nền khi chưa có ảnh')->options(Models\Ad::THEMES)->default('red'),
                Select::make('art')->label('Hình minh họa')->options(art_options())->searchable(),
                TextInput::make('meta_title')->label('Tiêu đề SEO')->maxLength(70),
                TextInput::make('meta_description')->label('Mô tả SEO')->maxLength(170),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Ảnh')->disk('public'),
                TextColumn::make('title')->label('Tiêu đề')->searchable()->wrap()->limit(70),
                TextColumn::make('category.name')->label('Chuyên mục')->badge()->color('gray'),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => $state === 'published' ? 'Đã đăng' : 'Bản nháp')
                    ->color(fn ($state) => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('views')->label('Lượt xem')->sortable(),
                TextColumn::make('published_at')->label('Ngày đăng')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([SelectFilter::make('post_category_id')->label('Chuyên mục')->relationship('category', 'name')])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
