<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public const DEFAULTS = [
        'site_name' => 'NEXA', 'site_tagline' => 'điện máy', 'logo' => '', 'favicon' => '',
        'hotline_sales' => '1900 0000', 'hotline_care' => '1800 0000',
        'email' => 'cskh@example.com', 'address' => 'Cập nhật địa chỉ cửa hàng trong trang quản trị',
        'company_name' => 'Tên công ty của bạn', 'tax_code' => '',
        'top_strip' => 'Giao nhanh 2 giờ nội thành|Lắp đặt miễn phí|Đổi trả trong 30 ngày',
        'free_ship_from' => '2000000', 'shipping_fee' => '30000',
        'bank_info' => 'Ngân hàng ABC - STK 0000 0000 0000 - Chủ TK: TEN CONG TY',
        'facebook' => '', 'youtube' => '', 'tiktok' => '', 'zalo' => '',
        'meta_description' => 'Mua điện máy, điện lạnh, gia dụng chính hãng giá tốt. Giao nhanh, lắp đặt miễn phí, trả góp 0%.',
        'hot_keywords' => 'Tivi 55 inch|Điều hòa 1 HP|Máy giặt 9 kg|Tủ lạnh Inverter|Máy sưởi dầu|Bếp từ đôi|Nồi chiên không dầu|Robot hút bụi|Máy lọc nước RO|Bình nóng lạnh',
    ];

    public static function allCached(): array
    {
        // Giá trị mặc định nằm ngoài cache để các mục cấu hình mới luôn có sẵn sau khi nâng cấp
        return array_merge(self::DEFAULTS, cache()->remember('settings', 600, fn () => self::pluck('value', 'key')->all()));
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::allCached()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        cache()->forget('settings');
    }
}
