<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Enum;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use PHPUnit\Framework\TestCase;

class RoundingModeTest extends TestCase
{
    public function test_values(): void
    {
        $values = RoundingMode::values();
        $this->assertCount(5, $values);
        $this->assertContains(RoundingMode::HALF_AWAY_FROM_ZERO, $values);
        $this->assertContains(RoundingMode::HALF_TOWARDS_ZERO, $values);
        $this->assertContains(RoundingMode::HALF_EVEN, $values);
        $this->assertContains(RoundingMode::UP, $values);
        $this->assertContains(RoundingMode::DOWN, $values);
    }

    public function test_tryFrom(): void
    {
        $this->assertSame(RoundingMode::HALF_AWAY_FROM_ZERO, RoundingMode::tryFrom('haway'));
        $this->assertSame(RoundingMode::HALF_TOWARDS_ZERO, RoundingMode::tryFrom('hzero'));
        $this->assertSame(RoundingMode::HALF_EVEN, RoundingMode::tryFrom('heven'));
        $this->assertSame(RoundingMode::UP, RoundingMode::tryFrom('up'));
        $this->assertSame(RoundingMode::DOWN, RoundingMode::tryFrom('down'));
        $this->assertNull(RoundingMode::tryFrom('invalid'));
    }

    public static function roundDataProvider(): array
    {
        return [
            [RoundingMode::HALF_AWAY_FROM_ZERO, 1.5, 2],
            [RoundingMode::HALF_AWAY_FROM_ZERO, -1.5, -2],
            [RoundingMode::HALF_TOWARDS_ZERO, 1.5, 1],
            [RoundingMode::HALF_TOWARDS_ZERO, -1.5, -1],
            [RoundingMode::HALF_EVEN, 1.5, 2],
            [RoundingMode::HALF_EVEN, 2.5, 2],
            [RoundingMode::HALF_EVEN, 3.5, 4],
            [RoundingMode::UP, 1.1, 2],
            [RoundingMode::UP, -1.1, -2],
            [RoundingMode::DOWN, 1.9, 1],
            [RoundingMode::DOWN, -1.9, -1],
        ];
    }

    /**
     * @dataProvider roundDataProvider
     */
    public function test_round(string $mode, float|int $input, int $expected): void
    {
        $this->assertSame($expected, RoundingMode::round($mode, $input));
    }

    public function test_round_invalid_mode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        RoundingMode::round('invalid', 1.5);
    }
}
