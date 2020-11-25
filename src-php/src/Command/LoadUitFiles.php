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
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LoadUitFiles extends Command
{
    protected static $defaultName = 'app:load-uit-files';

    private EntityManagerInterface $em;
    private array $sensorParameters = [];

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription('Reads UIT-CSV-Files and saves data to database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $folderWithNewCSVFiles = __DIR__ . '/../../data/UIT/inbox';
        $folderWithErroredCSVFiles = __DIR__ . '/../../data/UIT/error';
        $folderWithProcessedCSVFiles = __DIR__ . '/../../data/UIT/archive';

        $fs = new Filesystem();
        if (!$fs->exists($folderWithNewCSVFiles)) {
            $fs->mkdir($folderWithNewCSVFiles);
        }
        if (!$fs->exists($folderWithErroredCSVFiles)) {
            $fs->mkdir($folderWithErroredCSVFiles);
        }
        if (!$fs->exists($folderWithProcessedCSVFiles)) {
            $fs->mkdir($folderWithProcessedCSVFiles);
        }

        $finder = new Finder();
        $finder->sortByName();
        $finder->files()->in($folderWithNewCSVFiles);
        if (!$finder->hasResults()) {
            $output->writeln('No files found.');
            return Command::SUCCESS;
        }

        $sensor = null;
        foreach ($finder as $file) {
            $csvFile = $file->getRealPath();
            $fileName = $file->getFilenameWithoutExtension();

            if ($fs->exists(sprintf("%s/%s", $folderWithProcessedCSVFiles, $file->getFilename()))) {
                $fs->remove(sprintf("%s/%s", $folderWithNewCSVFiles, $file->getFilename()));
                continue;
            }

            $output->writeln('Read file: ' . $file->getFilename());

            try {
                [$projectName, $sensorName, $timestamp] = explode('_', $fileName);
            } catch (Exception $e) {
                $output->writeln('Filename not correctly formatted.');
                $fs->rename(
                    sprintf("%s/%s", $folderWithNewCSVFiles, $file->getFilename()),
                    sprintf("%s/%s", $folderWithErroredCSVFiles, $file->getFilename()),
                );
                continue;
            }

            if ($sensor === null) {
                $sensor = $this->loadSensor($projectName, $sensorName, $output);
                $this->sensorParameters = [];
                foreach ($sensor->parameters() as $parameter) {
                    $this->sensorParameters[$parameter->name()] = $parameter;
                }
            }

            if ($sensor->name() !== $sensorName || $sensor->project() !== $projectName) {
                $sensor = $this->loadSensor($projectName, $sensorName, $output);
                $this->sensorParameters = [];
                foreach ($sensor->parameters() as $parameter) {
                    $this->sensorParameters[$parameter->name()] = $parameter;
                }
            }

            try {
                $data = $this->readDataFromFile($csvFile);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
                $fs->rename(
                    sprintf("%s/%s", $folderWithNewCSVFiles, $file->getFilename()),
                    sprintf("%s/%s", $folderWithErroredCSVFiles, $file->getFilename()),
                );
                continue;
            }

            $keys = array_keys($data);

            foreach ($keys as $parameterName) {
                $parameter = $this->sensorParameters[$parameterName] ?? null;
                if (null === $parameter) {
                    $parameter = Parameter::fromTypeAndName($parameterName, $parameterName);
                    $parameter->setSensor($sensor);
                    $this->em->persist($parameter);
                    $this->sensorParameters[$parameter->name()] = $parameter;
                }

                foreach ($data[$parameterName] as $key => $dtVal) {
                    $data[$parameterName][$key] = DateTimeValue::fromDateTimeValue(
                        new DateTime($dtVal[0]),
                        $dtVal[1]
                    );
                }

                $dataset = DataSet::fromDatasourceWithData(DataSource::fromCsvUit(), $data[$parameterName], $file->getFilename());
                $dataset->setParameter($parameter);
                $this->em->persist($dataset);
            }

            $this->em->flush();

            if ($fs->exists(sprintf("%s/%s", $folderWithProcessedCSVFiles, $file->getFilename()))) {
                $fs->remove(sprintf("%s/%s", $folderWithProcessedCSVFiles, $file->getFilename()));
            }

            $fs->rename(
                sprintf("%s/%s", $folderWithNewCSVFiles, $file->getFilename()),
                sprintf("%s/%s", $folderWithProcessedCSVFiles, $file->getFilename()),
            );
        }

        return Command::SUCCESS;
    }

    private function get_between($input, $start, $end)
    {
        return substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));
    }

    /**
     * @param string $absoluteFileName
     * @return array
     * @throws Exception
     */
    private function readDataFromFile(string $absoluteFileName): array
    {
        $data = [];
        $header = null;
        foreach (file($absoluteFileName) as $line) {
            if ($header === null) {
                $headerLine = str_getcsv($line, ';');
                $header = [];
                foreach ($headerLine as $key => $headerCell) {
                    if ($key === 0) {
                        $header[] = 'date_time';
                        continue;
                    }
                    $parameterName = $this->get_between($headerCell, '{', '}');
                    if ($parameterName === false || strlen($parameterName) === 0) {
                        throw new Exception('Wrong Header Format.');
                    }
                    $header[] = $parameterName;
                }
                continue;
            }
            $lineData = str_getcsv($line, ';');

            foreach ($lineData as $key => $lineDatum) {
                if ($key === 0) {
                    $lineData[$key] = DateTime::createFromFormat('d.m.Y H:i:s', $lineDatum)->format(DATE_ATOM);
                    continue;
                }

                $lineData[$key] = (float)$lineDatum;
            }

            $data[] = array_combine($header, $lineData);
        }

        $returnMatrix = [];
        foreach ($header as $parameterName) {
            if ($parameterName === 'date_time') {
                continue;
            }

            $returnMatrix[$parameterName] = [];
            foreach ($data as $rowNr => $row) {
                $returnMatrix[$parameterName][] = [$row['date_time'], $row[$parameterName]];
            }
        }

        return $returnMatrix;
    }

    private function loadSensor(string $projectName, string $sensorName, OutputInterface $output): Sensor
    {
        $sensor = $this->em->getRepository(Sensor::class)->findOneBy(['project' => $projectName, 'name' => $sensorName]);
        if (null === $sensor) {
            $output->writeln('Sensor not found, create a new one.');
            $sensor = Sensor::fromProjectNameAndLocation($projectName, $sensorName, '');
        }

        return $sensor;
    }
}
