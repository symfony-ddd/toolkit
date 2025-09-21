<?php

namespace SymfonyDDD\CoreBundle\Tests\Unit\ValueObject;

use PHPUnit\Framework\TestCase;
use SymfonyDDD\CoreBundle\Tests\Unit\ValueObject\Mock\Delay;

class DelayTest extends TestCase
{
    public function testEqualityComparison(): void
    {
        $delay1 = Delay::fromMilliseconds(1000);
        $delay2 = Delay::fromMilliseconds(1000);
        $delay3 = Delay::fromMilliseconds(500);

        $this->assertTrue($delay1->eq($delay2));
        $this->assertFalse($delay1->eq($delay3));
    }

    public function testInequalityComparison(): void
    {
        $delay1 = Delay::fromMilliseconds(1000);
        $delay2 = Delay::fromMilliseconds(1000);
        $delay3 = Delay::fromMilliseconds(500);

        $this->assertFalse($delay1->neq($delay2));
        $this->assertTrue($delay1->neq($delay3));
    }

    public function testGreaterThanComparison(): void
    {
        $delay1 = Delay::fromMilliseconds(1000);
        $delay2 = Delay::fromMilliseconds(500);
        $delay3 = Delay::fromMilliseconds(1000);
        $delay4 = Delay::fromMilliseconds(1500);

        $this->assertTrue($delay1->gt($delay2));
        $this->assertFalse($delay1->gt($delay3));
        $this->assertFalse($delay1->gt($delay4));
    }

    public function testLessThanComparison(): void
    {
        $delay1 = Delay::fromMilliseconds(500);
        $delay2 = Delay::fromMilliseconds(1000);
        $delay3 = Delay::fromMilliseconds(500);
        $delay4 = Delay::fromMilliseconds(250);

        $this->assertTrue($delay1->lt($delay2));
        $this->assertFalse($delay1->lt($delay3));
        $this->assertFalse($delay1->lt($delay4));
    }

    public function testGreaterThanOrEqualComparison(): void
    {
        $delay1 = Delay::fromMilliseconds(1000);
        $delay2 = Delay::fromMilliseconds(500);
        $delay3 = Delay::fromMilliseconds(1000);
        $delay4 = Delay::fromMilliseconds(1500);

        $this->assertTrue($delay1->gte($delay2));
        $this->assertTrue($delay1->gte($delay3));
        $this->assertFalse($delay1->gte($delay4));
    }

    public function testLessThanOrEqualComparison(): void
    {
        $delay1 = Delay::fromMilliseconds(500);
        $delay2 = Delay::fromMilliseconds(1000);
        $delay3 = Delay::fromMilliseconds(500);
        $delay4 = Delay::fromMilliseconds(250);

        $this->assertTrue($delay1->lte($delay2));
        $this->assertTrue($delay1->lte($delay3));
        $this->assertFalse($delay1->lte($delay4));
    }

    public function testZeroDelayComparisons(): void
    {
        $zeroDelay = Delay::fromMilliseconds(0);
        $positiveDelay = Delay::fromMilliseconds(100);
        $anotherZeroDelay = Delay::fromMilliseconds(0);

        $this->assertTrue($zeroDelay->eq($anotherZeroDelay));
        $this->assertTrue($zeroDelay->lt($positiveDelay));
        $this->assertTrue($zeroDelay->lte($positiveDelay));
        $this->assertFalse($zeroDelay->gt($positiveDelay));
        $this->assertFalse($zeroDelay->gte($positiveDelay));
    }

    public function testLargeDelayComparisons(): void
    {
        $largeDelay1 = Delay::fromMilliseconds(999999);
        $largeDelay2 = Delay::fromMilliseconds(1000000);

        $this->assertTrue($largeDelay1->lt($largeDelay2));
        $this->assertTrue($largeDelay1->lte($largeDelay2));
        $this->assertTrue($largeDelay2->gt($largeDelay1));
        $this->assertTrue($largeDelay2->gte($largeDelay1));
        $this->assertTrue($largeDelay1->neq($largeDelay2));
    }

    public function testNegativeDelayThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delay cannot be negative');

        Delay::fromMilliseconds(-100);
    }

    public function testComparisonChaining(): void
    {
        $small = Delay::fromMilliseconds(100);
        $medium = Delay::fromMilliseconds(500);
        $large = Delay::fromMilliseconds(1000);

        $this->assertTrue($small->lt($medium) && $medium->lt($large));
        $this->assertTrue($large->gt($medium) && $medium->gt($small));
        $this->assertTrue($small->lte($medium) && $medium->lte($large));
        $this->assertTrue($large->gte($medium) && $medium->gte($small));
    }
}
