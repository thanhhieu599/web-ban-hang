<?php

namespace App\Filament\Resources\Staff;

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

class StaffResource extends Resource
{
    protected static ?string $model = Models\User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Hệ thống';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'nhân sự';

    protected static ?string $pluralModelLabel = 'Tài khoản quản trị';

    protected static ?string $navigationLabel = 'Tài khoản quản trị';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'staff';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Họ tên')->required(),
            TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('phone')->label('Số điện thoại')->tel(),
            TextInput::make('password')->label('Mật khẩu')->password()->revealable()->minLength(8)
                ->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state))
                ->helperText('Khi sửa, để trống nếu không đổi mật khẩu.'),
            Toggle::make('is_active')->label('Cho phép đăng nhập')->default(true)->inline(false),
            Select::make('role')->label('Vai trò')->options(['super_admin' => 'Super Admin (toàn quyền)', 'admin' => 'Admin (vận hành cửa hàng)'])->default('admin')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Họ tên')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('role')->label('Vai trò')->badge()->formatStateUsing(fn ($state) => Models\User::ROLES[$state] ?? $state)
                    ->color(fn ($state) => $state === 'super_admin' ? 'danger' : 'info'),
                ToggleColumn::make('is_active')->label('Hoạt động')->disabled(fn ($record) => $record->id === auth()->id()),
                TextColumn::make('created_at')->label('Ngày tạo')->dateTime('d/m/Y')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()->hidden(fn ($record) => $record->id === auth()->id())]);
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('role', ['super_admin', 'admin']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStaff::route('/'),
        ];
    }
}
