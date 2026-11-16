<?php

namespace App\Filament\Resources\Contacts;

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

class ContactResource extends Resource
{
    protected static ?string $model = Models\Contact::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static string|\UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'liên hệ';

    protected static ?string $pluralModelLabel = 'Liên hệ';

    protected static ?string $navigationLabel = 'Liên hệ';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Họ tên')->required(),
            TextInput::make('phone')->label('Số điện thoại'),
            TextInput::make('email')->label('Email'),
            TextInput::make('subject')->label('Chủ đề'),
            Textarea::make('message')->label('Nội dung')->required()->rows(4)->columnSpanFull(),
            Select::make('status')->label('Trạng thái')->options(Models\Contact::STATUSES)->default('new')->required(),
            Textarea::make('reply')->label('Ghi chú phản hồi')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Người gửi')->searchable()->description(fn ($record) => $record->phone ?: $record->email),
                TextColumn::make('subject')->label('Chủ đề')->limit(40),
                TextColumn::make('message')->label('Nội dung')->limit(70)->wrap(),
                TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Models\Contact::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['new' => 'warning', 'read' => 'info', 'replied' => 'success'][$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Ngày gửi')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([SelectFilter::make('status')->label('Trạng thái')->options(Models\Contact::STATUSES)])
            ->recordActions([
                Action::make('reply')->label('Trả lời qua email')->icon('heroicon-o-paper-airplane')->color('primary')
                    ->visible(fn ($record) => filled($record->email))
                    ->schema([Textarea::make('reply')->label('Nội dung trả lời')->required()->rows(6)
                        ->default(fn ($record) => "Chào {$record->name},\n\nCảm ơn bạn đã liên hệ.\n\n")])
                    ->action(function ($record, array $data) {
                        try {
                            \Mail::raw($data['reply']."\n\n--\n".setting('site_name').' · '.setting('hotline_care'), fn ($m) => $m->to($record->email)->subject('Re: '.($record->subject ?: 'Liên hệ của bạn')));
                            $record->update(['status' => 'replied', 'reply' => $data['reply']]);
                            Notification::make()->title('Đã gửi email trả lời')->success()->send();
                        } catch (\Throwable $e) {
                            report($e);
                            Notification::make()->title('Không gửi được email. Kiểm tra cấu hình MAIL_ trong .env.')->danger()->send();
                        }
                    }),
                Action::make('read')->label('Đánh dấu đã xem')->icon('heroicon-o-eye')->color('gray')
                    ->visible(fn ($record) => $record->status === 'new')->action(fn ($record) => $record->update(['status' => 'read'])),
                EditAction::make(), DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Models\Contact::where('status', 'new')->count() ?: null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContacts::route('/'),
        ];
    }
}
