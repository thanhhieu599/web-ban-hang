<?php

namespace App\Filament\Resources\Faqs;

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

class FaqResource extends Resource
{
    protected static ?string $model = Models\Faq::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'câu hỏi';

    protected static ?string $pluralModelLabel = 'Câu hỏi thường gặp';

    protected static ?string $navigationLabel = 'Câu hỏi thường gặp';

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('group')->label('Nhóm')->default('Chung')->required()->datalist(fn () => Models\Faq::distinct()->pluck('group')->all()),
            TextInput::make('sort')->label('Thứ tự')->numeric()->default(0),
            TextInput::make('question')->label('Câu hỏi')->required()->columnSpanFull(),
            Textarea::make('answer')->label('Trả lời')->required()->rows(4)->columnSpanFull(),
            Toggle::make('is_active')->label('Hiển thị')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')->label('Nhóm')->badge()->color('gray'),
                TextColumn::make('question')->label('Câu hỏi')->searchable()->wrap(),
                ToggleColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('sort')->label('Thứ tự')->sortable(),
            ])
            ->defaultSort('sort')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFaqs::route('/'),
        ];
    }
}
