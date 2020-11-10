<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Entity;

use App\Entity\DateTimeValue;
use DateTime;
use PHPUnit\Framework\TestCase;

class DateTimeValueTest extends TestCase
{

    public function testFromDateTimeValue(): void
    {
        $dateTime = new DateTime();
        $value = random_int(100000, 1000000 - 1) / 1000;
        $data = DateTimeValue::fromDateTimeValue($dateTime, $value);

        self::assertEquals($dateTime->getTimestamp(), $data->dateTime()->getTimestamp());
        self::assertEquals($dateTime->getTimestamp(), $data->timestamp());
        self::assertEquals($value, $data->value());
    }

    public function testFromTimestamp(): void
    {
        $timestamp = (new DateTime())->getTimestamp();
        $dateTime = new DateTime(sprintf("@%d", $timestamp));
        $value = random_int(100000, 1000000 - 1) / 1000;

        $data = DateTimeValue::fromTimestampValue($timestamp, $value);
        self::assertEquals($timestamp, $data->timestamp());
        self::assertEquals($dateTime, $data->dateTime());
        self::assertEquals($value, $data->value());
    }
}
