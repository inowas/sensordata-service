<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProcessing\WSVData\WsvApiStationResponse;
use App\Entity\DataSet;
use App\Entity\DateTimeValue;
use App\Entity\Parameter;
use App\Entity\Sensor;
use App\Model\DataSource;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseWSVOnline extends Command
{
    protected static $defaultName = 'app:parse-wsv';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription('Reads live data from the WSV-api.');
        $this->addArgument('stations', InputArgument::REQUIRED, 'The name, number or uuid of the stations, comma separated.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stations = $input->getArgument('stations');
        $url = sprintf('https://www.pegelonline.wsv.de/webservices/rest-api/v2/stations.json?ids=%s&includeTimeseries=true&includeCurrentMeasurement=true', $stations);
        $content = file_get_contents($url);
        if ($content === false) {
            $output->writeln('Station not found!');
            return Command::FAILURE;
        }

        $datasets = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        foreach ($datasets as $dataset) {
            $response = WsvApiStationResponse::fromArr($dataset);
            $projectName = 'WSV';
            $sensorName = $response->shortname();
            $shortName = 'W';

            $location = json_encode([
                'lat' => $response->latitude(),
                'lon' => $response->longitude(),
                'gaugeZero' => $response->getGauge($shortName) ?? ''
            ], JSON_THROW_ON_ERROR);

            $datetime = $response->getDateTime($shortName);

            if (!$datetime instanceof DateTime) {
                $output->writeln('DateTime not readable.');
                return Command::FAILURE;
            }

            $value = $response->getWaterLevelInMeters($shortName);
            $dateTimeValue = DateTimeValue::fromDateTimeValue($datetime, $value);

            $sensor = $this->loadSensor($projectName, $sensorName, $location, $output);
            $parameter = $sensor->getParameterWithName('water_level');
            if (null === $parameter) {
                $parameter = Parameter::fromTypeAndName('water_level', 'water_level');
                $parameter->setSensor($sensor);
                $dataset = DataSet::fromDatasourceWithData(DataSource::fromWSVApi(), [$dateTimeValue], '');
                $parameter->addDataSet($dataset);
                $dataset->setParameter($parameter);
            }

            /** @var DataSet $dataset */
            $dataset = $parameter->dataSets()[count($parameter->dataSets()) - 1];
            $dataset->addDateTimeValue($dateTimeValue);

            $this->em->persist($sensor);
            $this->em->persist($parameter);
            $this->em->persist($dataset);
            $this->em->persist($dateTimeValue);
            $this->em->flush();
            $output->writeln('Sensorvalue successfully saved.');
        }

        return Command::SUCCESS;
    }

    private function loadSensor(string $projectName, string $sensorName, string $location, OutputInterface $output): Sensor
    {
        $sensor = $this->em->getRepository(Sensor::class)->findOneBy(['project' => $projectName, 'name' => $sensorName]);
        if (null === $sensor) {
            $output->writeln('Sensor not found, create a new one.');
            $sensor = Sensor::fromProjectNameAndLocation($projectName, $sensorName, $location);
        }

        return $sensor;
    }
}
