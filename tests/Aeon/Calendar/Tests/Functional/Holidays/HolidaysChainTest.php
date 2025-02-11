<?php

declare(strict_types=1);

namespace Aeon\Calendar\Tests\Functional\Holidays;

use Aeon\Calendar\Gregorian\Day;
use Aeon\Calendar\Gregorian\Holidays;
use Aeon\Calendar\Gregorian\Holidays\HolidaysChain;
use PHPUnit\Framework\TestCase;

final class HolidaysChainTest extends TestCase
{
    public function test_is_holiday_without_providers() : void
    {
        $this->assertFalse((new HolidaysChain())->isHoliday(Day::fromString('2020-01-01')));
    }

    public function test_holidays_at_without_providers() : void
    {
        $this->assertSame([], (new HolidaysChain())->holidaysAt(Day::fromString('2020-01-01')));
    }

    public function test_is_holiday_with_2_providers() : void
    {
        $provider1 = $this->createStub(Holidays::class);
        $provider1->method('isHoliday')
            ->willReturn(true);

        $provider2 = $this->createStub(Holidays::class);
        $provider2->method('isHoliday')
            ->willReturn(false);

        $this->assertTrue((new HolidaysChain($provider1, $provider2))->isHoliday(Day::fromString('2020-01-01')));
    }

    public function test_holiday_at_with_2_providers() : void
    {
        $provider1 = $this->createStub(Holidays::class);
        $provider1->method('holidaysAt')
            ->willReturn([new Holidays\Holiday(Day::fromString('2020-01-01'), new Holidays\HolidayName(new Holidays\HolidayLocaleName('en', 'Holiday')))]);

        $provider2 = $this->createStub(Holidays::class);
        $provider2->method('holidaysAt')
            ->willReturn([]);

        $this->assertCount(1, (new HolidaysChain($provider1, $provider2))->holidaysAt(Day::fromString('2020-01-01')));
        $this->assertSame('Holiday', (new HolidaysChain($provider1, $provider2))->holidaysAt(Day::fromString('2020-01-01'))[0]->name());
    }

    public function test_holiday_at_with_2_providers_duplicating_holidays() : void
    {
        $provider1 = $this->createStub(Holidays::class);
        $provider1->method('holidaysAt')
            ->willReturn([new Holidays\Holiday(Day::fromString('2020-01-01'), new Holidays\HolidayName(new Holidays\HolidayLocaleName('en', 'Holiday')))]);

        $provider2 = $this->createStub(Holidays::class);
        $provider2->method('holidaysAt')
            ->willReturn([new Holidays\Holiday(Day::fromString('2020-01-01'), new Holidays\HolidayName(new Holidays\HolidayLocaleName('en', 'Holiday')))]);

        $this->assertCount(2, (new HolidaysChain($provider1, $provider2))->holidaysAt(Day::fromString('2020-01-01')));
        $this->assertSame('Holiday', (new HolidaysChain($provider1, $provider2))->holidaysAt(Day::fromString('2020-01-01'))[0]->name());
        $this->assertSame('Holiday', (new HolidaysChain($provider1, $provider2))->holidaysAt(Day::fromString('2020-01-01'))[1]->name());
    }
}
