<?php

declare(strict_types=1);

namespace App\Tests\IntegrationTests\Entity;

use App\Entity\DataSet;
use App\Entity\DateTimeValue;
use App\Entity\Parameter;
use App\Entity\Sensor;
use App\Model\DataSource;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;

class SensorTest extends KernelTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Serializer */
    private $serializer;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->serializer = $kernel->getContainer()->get('serializer');
    }

    public function testSensorIntegration(): void
    {
        $dataSet = DataSet::fromDatasourceWithData(
            DataSource::fromCsvUit(), [
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-01'), 20.101),
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-02'), 20.102),
            DateTimeValue::fromDateTimeValue(new DateTime('2020-10-03'), 20.103),
        ]);
        $parameter = Parameter::fromTypeAndName('t', 'temperature')->addDataSet($dataSet);
        $sensor = Sensor::fromProjectNameAndLocation('testProject', 'testName', 'testLocation')->addParameter($parameter);

        $this->entityManager->persist($sensor);
        $this->entityManager->flush();

        /** @var Sensor $loadedSensor */
        $loadedSensor = $this->entityManager->getRepository(Sensor::class)->findOneBy(['id' => $sensor->id()]);
        self::assertInstanceOf(Sensor::class, $loadedSensor);

        /* Serialize sensor_list */
        $normalizedData = $this->serializer->normalize($loadedSensor, null, ['groups' => 'sensor_list']);
        $expectedNormalizedData = [
            'id' => $loadedSensor->id()->toString(),
            'project' => 'testProject',
            'name' => 'testName',
            'location' => 'testLocation',
            'parameterList' => ['t']
        ];
        self::assertEquals($expectedNormalizedData, $normalizedData);
        self::assertEquals($expectedNormalizedData, json_decode($this->serializer->serialize($loadedSensor, 'json', ['groups' => 'sensor_list']), true));

        /* Serialize sensor_details */
        $normalizedData = $this->serializer->normalize($loadedSensor, null, ['groups' => 'sensor_details']);
        $expectedNormalizedData = [
            'id' => $loadedSensor->id()->toString(),
            'project' => 'testProject',
            'name' => 'testName',
            'location' => 'testLocation',
            'parameters' => [
                [
                    'id' => $parameter->id()->toString(),
                    'type' => 't',
                    'name' => 'temperature'
                ]
            ]
        ];
        self::assertEquals($expectedNormalizedData, $normalizedData);
        self::assertEquals($expectedNormalizedData, json_decode($this->serializer->serialize($loadedSensor, 'json', ['groups' => 'sensor_details']), true));


        /** @var Parameter $loadedParameter */
        $loadedParameter = $loadedSensor->parameters()->first();
        self::assertEquals($parameter->id()->toString(), $loadedParameter->id()->toString());

        /* Serialize parameter_details */
        $normalizedData = $this->serializer->normalize($loadedParameter, null, ['groups' => 'parameter_details']);
        $expectedNormalizedData = [
            'id' => $parameter->id()->toString(),
            'sensorId' => $sensor->id()->toString(),
            'type' => 't',
            'name' => 'temperature',
            'dataSets' => [
                [
                    'id' => $dataSet->id(),
                    'first' => '2020-10-01T00:00:00+00:00',
                    'last' => '2020-10-03T00:00:00+00:00',
                    'numberOfValues' => 3,
                    'dataSource' => 0
                ]
            ]
        ];
        self::assertEquals($expectedNormalizedData, $normalizedData);
        self::assertEquals($expectedNormalizedData, json_decode($this->serializer->serialize($loadedParameter, 'json', ['groups' => 'parameter_details']), true));

        /* Serialize parameter_details */
        $normalizedData = $this->serializer->normalize($loadedParameter, null, ['groups' => 'parameter_data']);
        $expectedNormalizedData = [
            'id' => $parameter->id()->toString(),
            'sensorId' => $sensor->id()->toString(),
            'type' => 't',
            'name' => 'temperature',
            'data' => [
                [1601510400, 20.101],
                [1601596800, 20.102],
                [1601683200, 20.103]
            ]
        ];
        self::assertEquals($expectedNormalizedData, $normalizedData);
        self::assertEquals($expectedNormalizedData, json_decode($this->serializer->serialize($loadedParameter, 'json', ['groups' => 'parameter_data']), true));
    }
}
