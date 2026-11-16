<?php

namespace App\Filament\Resources\Expenses;

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

class ExpenseResource extends Resource
{
    protected static ?string $model = Models\Expense::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Tài chính';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'khoản chi';

    protected static ?string $pluralModelLabel = 'Chi phí';

    protected static ?string $navigationLabel = 'Chi phí';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Nội dung chi')->required()->columnSpanFull(),
            Select::make('category')->label('Nhóm chi phí')->options(array_combine(Models\Expense::CATEGORIES, Models\Expense::CATEGORIES))->default('Khác')->required(),
            TextInput::make('amount')->label('Số tiền')->numeric()->required()->suffix('đ'),
            DatePicker::make('spent_at')->label('Ngày chi')->default(now())->required(),
            TextInput::make('note')->label('Ghi chú'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('spent_at')->label('Ngày')->date('d/m/Y')->sortable(),
                TextColumn::make('title')->label('Nội dung')->searchable()->wrap(),
                TextColumn::make('category')->label('Nhóm')->badge()->color('gray'),
                TextColumn::make('amount')->label('Số tiền')->sortable()->formatStateUsing(fn ($state) => vnd($state))
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Tổng')->formatStateUsing(fn ($state) => vnd($state))),
            ])
            ->defaultSort('spent_at', 'desc')
            ->filters([SelectFilter::make('category')->label('Nhóm')->options(array_combine(Models\Expense::CATEGORIES, Models\Expense::CATEGORIES))])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageExpenses::route('/'),
        ];
    }
}
