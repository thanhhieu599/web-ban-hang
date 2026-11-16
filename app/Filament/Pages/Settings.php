<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Settings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Hệ thống';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Cấu hình cửa hàng';

    protected static ?string $navigationLabel = 'Cấu hình cửa hàng';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function mount(): void
    {
        $this->form->fill(Setting::allCached());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Thông tin cửa hàng')->columns(2)->schema([
                TextInput::make('site_name')->label('Tên thương hiệu')->required(),
                TextInput::make('site_tagline')->label('Dòng chữ dưới logo'),
                TextInput::make('hotline_sales')->label('Tổng đài mua hàng'),
                TextInput::make('hotline_care')->label('Tổng đài chăm sóc khách hàng'),
                TextInput::make('email')->label('Email liên hệ')->email(),
                TextInput::make('address')->label('Địa chỉ'),
                TextInput::make('company_name')->label('Tên công ty (chân trang, hóa đơn)'),
                TextInput::make('tax_code')->label('Mã số thuế'),
            ]),
            Section::make('Logo & biểu tượng')->columns(2)->schema([
                FileUpload::make('logo')->label('Logo')->image()->imageEditor()->disk('public')->directory('brand')->maxSize(1024)
                    ->helperText('Ảnh ngang, cao khoảng 80 px, nền trong suốt. Để trống thì hiện logo chữ mặc định.'),
                FileUpload::make('favicon')->label('Biểu tượng trên tab trình duyệt (favicon)')->image()->disk('public')->directory('brand')->maxSize(256)
                    ->helperText('Ảnh vuông 64×64 px (PNG).'),
            ]),
            Section::make('Bán hàng')->columns(2)->schema([
                TextInput::make('shipping_fee')->label('Phí giao hàng mặc định')->numeric()->suffix('đ'),
                TextInput::make('free_ship_from')->label('Miễn phí giao hàng cho đơn từ')->numeric()->suffix('đ'),
                Textarea::make('bank_info')->label('Thông tin chuyển khoản')->rows(2)->columnSpanFull(),
            ]),
            Section::make('Giao diện & SEO')->schema([
                TextInput::make('top_strip')->label('Dải thông báo đầu trang')->helperText('Ngăn cách các ý bằng dấu |'),
                Textarea::make('hot_keywords')->label('Từ khóa tìm kiếm nổi bật')->rows(2)->helperText('Ngăn cách bằng dấu |'),
                Textarea::make('meta_description')->label('Mô tả SEO mặc định')->rows(2)->maxLength(170),
            ]),
            Section::make('Mạng xã hội')->columns(2)->schema([
                TextInput::make('facebook')->label('Facebook')->url(),
                TextInput::make('youtube')->label('YouTube')->url(),
                TextInput::make('tiktok')->label('TikTok')->url(),
                TextInput::make('zalo')->label('Zalo')->url(),
            ]),
        ]);
    }

    public function save(): void
    {
        Setting::put(array_map(fn ($v) => (string) (is_array($v) ? (reset($v) ?: '') : ($v ?? '')), $this->form->getState()));
        Notification::make()->title('Đã lưu cấu hình')->success()->send();
    }
}
