<?php

declare(strict_types=1);

namespace App\Command;

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

class ParseAmpeqSensorsBra2 extends Command
{
    private const PROJECT_NAME = 'BRA2';
    private const PARAMETERNAME = 'h';
    private array $sensorMap = [
        'PT-01' => '0550000000000011',
        'PT-02' => '0550000000000012',
        'PZ-07' => '0550000000000013',
        'PZ-05' => '0550000000000014',
        'PZ-04' => '0550000000000015',
        'PZ-06' => '0550000000000016',
        'PZ-03' => '0550000000000017',
        'PZ-02' => '0550000000000018',
        'PZ-01' => '0550000000000024',
        'EPA-11' => '0550000000000027',
        'EPA-12' => '0550000000000028',
        'EPA-13' => '0550000000000029',
    ];


    protected static $defaultName = 'app:parse-ampeq:bra2';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription('Reads from the ampeq-api. From the startdate max 14 days of data will be outputted.');
        $this->addArgument('id', InputArgument::REQUIRED, 'The measurement-id.');
        $this->addArgument('start', InputArgument::OPTIONAL, 'The unix-timestamp for the start date ot latest.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $output->writeln('-------------------------------');
        $id = $input->getArgument('id');

        $output->writeln(sprintf('Read data for Sensor: %s', $id));
        $start = $input->getArgument('start');

        if (!array_key_exists($id, $this->sensorMap)) {
            $output->writeln(sprintf('Sensor with id %s not found!', $id));
            $output->writeln('-------------------------------');
            return Command::FAILURE;
        }


        if ($start === null) {
            $output->writeln('No start value given, read latest value:');
            /** @var Sensor $sensor */
            $sensor = $this->em->getRepository(Sensor::class)->findOneBy(['name' => $id]);
            if (!$sensor instanceof Sensor) {
                $output->writeln(sprintf('Sensor with name %s not found!', $id));
                $output->writeln('-------------------------------');
                return Command::FAILURE;
            }

            $parameter = $sensor->getParameterWithName(self::PARAMETERNAME);

            if (!$parameter instanceof Parameter) {
                $output->writeln(sprintf('Parameter with name %s not found!', self::PARAMETERNAME));
                return Command::FAILURE;
            }

            if ($parameter->countDatasets() === 0) {
                $output->writeln(sprintf('No dataset saved already'));
                $output->writeln('-------------------------------');
                return Command::FAILURE;
            }

            $latestDataset = $this->em->getRepository(DataSet::class)->findOneBy(
                ['parameter' => $parameter->id()],
                ['id' => 'DESC']
            );

            if (!$latestDataset instanceof DataSet) {
                $output->writeln(sprintf('No lastest dataset found'));
                $output->writeln('-------------------------------');
                return Command::FAILURE;
            }

            $latest = $latestDataset->lastDateTime()->getTimestamp();
            $output->writeln(sprintf('Latest value: %s', $latest));
            $start = $latest + 1;
            $output->writeln(sprintf('New start value: %s', $start));
            $output->writeln(sprintf('New DateTime: %s', $dt = (new DateTime('@' . $start))->format(DATE_ATOM)));
        }

        $measurePoint = $this->sensorMap[$id];
        $url = sprintf('http://ampeq.net/metrics/metrics.php?measure_point=%s&start=%s', $measurePoint, $start);
        $content = file_get_contents($url);
        if ($content === false) {
            $output->writeln('Measurement not found!');
            $output->writeln('-------------------------------');
            return Command::FAILURE;
        }

        $dateTimeValues = $this->readDateTimeValuesFromContentString($content);
        $output->writeln(sprintf('Got %s new data values.', count($dateTimeValues)));

        if (count($dateTimeValues) === 0) {
            $output->writeln('No newer data found.');
            return Command::SUCCESS;
        }

        $sensorName = $id;
        $projectName = self::PROJECT_NAME;
        $parameterName = self::PARAMETERNAME;

        $sensor = $this->loadSensor($projectName, $sensorName, $output);
        $parameter = $sensor->getParameterWithName($parameterName);

        if (null === $parameter) {
            $parameter = Parameter::fromTypeAndName($parameterName, $parameterName);
            $parameter->setSensor($sensor);
            $this->em->persist($parameter);
        }

        $dataset = DataSet::fromDatasourceWithData(DataSource::fromAmpeqApi(), $dateTimeValues);
        $dataset->setParameter($parameter);
        $this->em->persist($dataset);
        $this->em->flush();

        $output->writeln('Saved!');
        $output->writeln('-------------------------------');

        return Command::SUCCESS;
    }

    private function readDateTimeValuesFromContentString(string $content): array
    {
        $dateTimeValues = [];
        $lineSeparator = "\r\n";
        if (strpos($content, $lineSeparator) === false) {
            return $dateTimeValues;
        }

        $lines = explode($lineSeparator, $content);
        foreach ($lines as $idx => $line) {
            if ($idx === 0 || $line === '') {
                continue;
            }

            [$timestamp, $value] = explode(";", $line);
            $dateTimeValues[] = DateTimeValue::fromTimestampValue((int)$timestamp, (float)$value);
        }

        return $dateTimeValues;
    }

    private function loadSensor(string $projectName, string $sensorName, OutputInterface $output): Sensor
    {
        $sensor = $this->em->getRepository(Sensor::class)->findOneBy(['project' => $projectName, 'name' => $sensorName]);
        if (null === $sensor) {
            $output->writeln('Sensor not found, create a new one.');
            $sensor = Sensor::fromProjectNameAndLocation($projectName, $sensorName);
        }

        return $sensor;
    }
}
