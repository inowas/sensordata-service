<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Entity;

use App\Entity\DataSet;
use App\Entity\DateTimeValue;
use App\Entity\Parameter;
use App\Model\DataSource;
use DateTime;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testInstantiate(): void
    {
        $type = 'testType';
        $name = 'testName';
        $p = Parameter::fromTypeAndName($type, $name);
        self::assertInstanceOf(Parameter::class, $p);
        self::assertEquals($type, $p->type());
        self::assertEquals($name, $p->name());
    }

    public function testGetData(): void
    {
        $type = 'testType';
        $name = 'testName';
        $p = Parameter::fromTypeAndName($type, $name);
        self::assertInstanceOf(Parameter::class, $p);
        self::assertEquals($type, $p->type());
        self::assertEquals($name, $p->name());

        $dataSet = DataSet::fromDatasourceWithData(
            DataSource::fromCsvUit(), [
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-01'), 20.101),
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-02'), 20.102),
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-03'), 20.103),
        ]);

        $p->addDataSet($dataSet);
        self::assertCount(1, $p->dataSets());
        self::assertEquals([
            [(new DateTime('2020-10-01'))->getTimestamp(), 20.101],
            [(new DateTime('2020-10-02'))->getTimestamp(), 20.102],
            [(new DateTime('2020-10-03'))->getTimestamp(), 20.103]
        ], $p->getData());;
    }
}
