<?php

namespace Tests\Unit;

use App\Models\Discount;
use PHPUnit\Framework\TestCase;

class DiscountTest extends TestCase
{
    public function test_percent_discount_respects_cap(): void
    {
        $d = new Discount(['type' => 'percent', 'value' => 10, 'max_discount' => 500000]);
        $this->assertSame(200000, $d->amountFor(2000000));
        $this->assertSame(500000, $d->amountFor(9000000));
    }

    public function test_fixed_discount_never_exceeds_subtotal(): void
    {
        $d = new Discount(['type' => 'fixed', 'value' => 300000]);
        $this->assertSame(300000, $d->amountFor(5000000));
        $this->assertSame(100000, $d->amountFor(100000));
    }

    public function test_minimum_order_and_usage_limit(): void
    {
        $d = new Discount(['is_active' => true, 'min_order' => 1000000, 'usage_limit' => 2, 'used' => 0]);
        $this->assertNotNull($d->invalidReason(500000));
        $this->assertNull($d->invalidReason(1500000));
        $d->used = 2;
        $this->assertSame('Mã giảm giá đã hết lượt sử dụng.', $d->invalidReason(1500000));
    }
}
