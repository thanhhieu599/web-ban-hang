<?php

use App\Models\Setting;

if (! function_exists('vnd')) {
    /** Định dạng tiền Việt: 1.290.000đ */
    function vnd(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', '.').'đ';
    }
}

if (! function_exists('setting')) {
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('short_count')) {
    /** 1850 -> 1,9k */
    function short_count(int $n): string
    {
        return $n >= 1000 ? str_replace('.', ',', (string) round($n / 1000, 1)).'k' : (string) $n;
    }
}

if (! function_exists('art_options')) {
    /** Danh sách hình minh họa có sẵn (dùng khi chưa tải ảnh thật). */
    function art_options(): array
    {
        $labels = [
            'washer' => 'Máy giặt cửa trước', 'dryer' => 'Máy sấy', 'toploader' => 'Máy giặt cửa trên', 'fridge' => 'Tủ lạnh', 'sbs' => 'Tủ lạnh side by side',
            'freezer' => 'Tủ đông', 'ac' => 'Điều hòa', 'tv' => 'Tivi', 'speaker' => 'Loa', 'fan' => 'Quạt', 'ricecooker' => 'Nồi cơm', 'airfryer' => 'Nồi chiên',
            'vacuum' => 'Máy hút bụi', 'robot' => 'Robot hút bụi', 'purifier' => 'Lọc không khí', 'microwave' => 'Lò vi sóng', 'oven' => 'Lò nướng', 'phone' => 'Điện thoại',
            'tablet' => 'Máy tính bảng', 'laptop' => 'Laptop', 'watch' => 'Đồng hồ', 'headphone' => 'Tai nghe', 'waterpurifier' => 'Máy lọc nước', 'stove' => 'Bếp từ',
            'blender' => 'Máy xay', 'dishwasher' => 'Máy rửa chén', 'kettle' => 'Ấm siêu tốc', 'heater' => 'Máy sưởi', 'waterheater' => 'Bình nóng lạnh', 'iron' => 'Bàn ủi',
            'hairdryer' => 'Máy sấy tóc', 'massage' => 'Máy massage', 'scale' => 'Cân sức khỏe', 'powerbank' => 'Sạc dự phòng', 'camera' => 'Camera', 'plug' => 'Ổ cắm', 'mouse' => 'Chuột',
        ];

        return collect(array_keys(config('art')))->mapWithKeys(fn ($k) => [$k => $labels[$k] ?? $k])->all();
    }
}
