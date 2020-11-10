<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Model;

use App\Model\DataSource;
use PHPUnit\Framework\TestCase;

class DataSourceTest extends TestCase
{
    public function intValueDataSourcesDataProvider(): array
    {
        return [
            [0], [1], [10], [11], [12], [30]
        ];
    }

    /**
     * @dataProvider intValueDataSourcesDataProvider
     */
    public function testIntValueDataSources(int $number): void
    {
        $dataSource = DataSource::fromInt($number);
        self::assertInstanceOf(DataSource::class, $dataSource);
        self::assertEquals($number, $dataSource->toInt());
    }
}
