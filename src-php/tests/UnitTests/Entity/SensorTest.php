<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Entity;

use App\Entity\Sensor;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class SensorTest extends TestCase
{
    public function testInstantiationWithoutParameters(): void
    {
        $project = 'testProject';
        $name = 'testName';
        $location = 'testLocation';

        $sensor = Sensor::fromProjectNameAndLocation($project, $name, $location);
        self::assertInstanceOf(Sensor::class, $sensor);
        self::assertInstanceOf(UuidInterface::class, $sensor->id());
        self::assertEquals($sensor->project(), $project);
        self::assertEquals($sensor->name(), $name);
        self::assertEquals($sensor->location(), $location);

        self::assertEquals([
            'id' => $sensor->id()->toString(),
            'name' => 'testName',
            'project' => 'testProject',
            'location' => 'testLocation',
            'parameters' => []
        ], $sensor->toArray());
    }
}
