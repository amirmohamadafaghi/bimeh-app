<?php

namespace Tests\Unit;

use App\Support\PersianNormalizer;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_persian_normalizer_formats_national_code_and_mobile_properly(): void
    {
        $this->assertSame('0859042642', PersianNormalizer::cleanNationalCode('۸۵۹۰۴۲۶۴۲'));
        $this->assertSame('09151371442', PersianNormalizer::cleanMobile('۹۱۵۱۳۷۱۴۴۲'));
        $this->assertSame('15000000', PersianNormalizer::cleanNumber('۱۵,۰۰۰,۰۰۰'));
    }
}
