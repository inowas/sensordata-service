<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Entity;

use App\Entity\DataSet;
use App\Entity\DateTimeValue;
use App\Model\DataSource;
use DateTime;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class DataSetTest extends TestCase
{
    public function testInstantiation(): void
    {
        $dataSource = DataSource::fromCsvUit();
        $data = [
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-01'), 20.101),
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-02'), 20.102),
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-03'), 20.103),
        ];

        $dataSet = DataSet::fromDatasourceWithData($dataSource, $data);
        self::assertInstanceOf(DataSet::class, $dataSet);
        self::assertEquals($dataSource, $dataSet->dataSource());
        self::assertInstanceOf(DateTime::class, $dataSet->createdAt());
        self::assertEquals($data, $dataSet->data());
        self::assertEquals(3, $dataSet->numberOfValues());
        self::assertEquals(new DateTime('2020-10-01'), $dataSet->firstDateTime());
        self::assertEquals(new DateTime('2020-10-03'), $dataSet->lastDateTime());
    }
}
