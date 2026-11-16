<?php

namespace App\Filament\Resources\Customers;

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

class CustomerResource extends Resource
{
    protected static ?string $model = Models\User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'khách hàng';

    protected static ?string $pluralModelLabel = 'Khách hàng';

    protected static ?string $navigationLabel = 'Khách hàng';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'customers';

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
            TextInput::make('address')->label('Địa chỉ')->columnSpanFull(),
            Hidden::make('role')->default('customer'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Họ tên')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->copyable(),
                TextColumn::make('phone')->label('SĐT')->searchable(),
                TextColumn::make('orders_count')->label('Số đơn')->counts('orders')->sortable(),
                TextColumn::make('orders_sum_total')->label('Tổng chi tiêu')->sum(['orders' => fn ($q) => $q->whereIn('status', Models\Order::REVENUE_STATUSES)], 'total')
                    ->formatStateUsing(fn ($state) => vnd($state))->sortable(),
                TextColumn::make('tier')->label('Hạng')->badge()
                    ->state(fn ($record) => ($record->orders_sum_total ?? 0) >= 50000000 ? 'Kim cương' : (($record->orders_sum_total ?? 0) >= 15000000 ? 'Vàng' : (($record->orders_count ?? 0) >= 2 ? 'Thân thiết' : 'Mới')))
                    ->color(fn ($state) => ['Kim cương' => 'info', 'Vàng' => 'warning', 'Thân thiết' => 'success'][$state] ?? 'gray'),
                ToggleColumn::make('is_active')->label('Hoạt động'),
                TextColumn::make('created_at')->label('Ngày đăng ký')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'customer');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }
}
