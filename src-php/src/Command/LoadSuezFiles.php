<?php

declare(strict_types=1);

namespace App\Command;

ini_set('memory_limit', '2048M');

use App\Entity\DataSet;
use App\Entity\DateTimeValue;
use App\Entity\Parameter;
use App\Entity\Sensor;
use App\Model\DataSource;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LoadSuezFiles extends Command
{
    protected static $defaultName = 'app:load-suez-files';

    private EntityManagerInterface $em;

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
        $folderWithNewCSVFiles = __DIR__ . '/../../data/Suez/inbox';
        $folderWithErroredCSVFiles = __DIR__ . '/../../data/Suez/error';
        $folderWithProcessedCSVFiles = __DIR__ . '/../../data/Suez/archive';

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

            if ($fs->exists(sprintf("%s/%s", $folderWithProcessedCSVFiles, $file->getFilename()))) {
                $fs->remove(sprintf("%s/%s", $folderWithNewCSVFiles, $file->getFilename()));
                continue;
            }

            $definedDataSets = [
                ['FRA1_72523_1' => 'Conductivité Brute {ec} [µS/m]'],
                ['FRA1_72523_2' => 'Niveau Brut {h} [m]'],
                ['FRA1_72525' => '[72525] Niveau Piezo 5 Gapeau {h} [m]'],
                ['FRA1_72526' => 'Niveau Brut {h} [m]'],
                ['FRA1_72527' => '[72527] Niveau Piezo 7 Gapeau {h} [m]'],
                ['FRA1_72529' => 'Niveau Brut {h} [m]'],
                ['FRA1_72530_1' => '[72530] Conductivité Piezo 11 Gapeau {ec} [µS/m]'],
                ['FRA1_72530_2' => '[72530] Niveau Piezo 11 Gapeau {h} [m]'],
                ['FRA1_72531_1' => 'Conductivité Brute {ec} [µS/m]'],
                ['FRA1_72531_2' => 'Niveau Brut {h} [m]'],
                ['FRA1_72532' => 'Niveau Brut {h} [m]'],
                ['FRA1_72533' => 'Niveau Brut {h} [m]'],
                ['FRA1_72534' => '[72534] Niveau Piezo 20 Gapeau {h} [m]'],
                ['FRA1_72480_1' => '[72480] Niveau NGF puits 2 Père Eternel {h} [m]'],
                ['FRA1_72480_2' => '(C) Volume jour Refoulement pompe 3 puits 2 (ViaQmesP3) {V} [m3]'],
                ['FRA1_72480_3' => '(C) Volume jour Refoulement pompe 4 puits 2  (ViaQmesP4) {V} [m3]'],
                ['FRA1_72480_4' => '(C) Volume jour Refoulement pompe 5 puits 2  (ViaQmesP5) {V} [m3]'],
                ['FRA1_72480_5' => '(C) Total jour prélèvement des puits Champ Père Eternel {V} [m3]'],
                ['FRA1_72481_1' => 'Conductivité Brute {ec} [mS/cm]'],
                ['FRA1_72481_2' => 'Niveau Brut {h} [m]'],
                ['FRA1_72481_3' => 'Niveau NGF puits 5 Golf Hotel {h} [m]'],
                ['FRA1_72481_4' => '(C) Volume jour Refoulement pompe 5 forage 5  (ViaQmesPx) {V} [m3]'],
                ['FRA1_72481_5' => '(C) Total jour prélèvement des puits Champ Golf Hotel {V} [m3]'],
                ['FRA1_88320_1' => 'Q62B20 - Conductivite Sirene - Q62TM_305 - DT {ec} [µS/m]'],
                ['FRA1_88320_2' => 'Q62B32 - Debit reseau - Q62TM_317 - DT {V} [m3]'],
                ['FRA1_88321_1' => 'Q63B12 - Niveau Nappe 1 - Q63TM_307 - DT {h} [m]'],
                ['FRA1_88321_2' => 'Q63B15 - Niveau Nappe 2 - Q63TM_310 - DT {h} [m]'],
                ['FRA1_88321_3' => 'Q63B11 - Niveau Bassin 1 - Q63TM_306 - DT {h} [m]'],
                ['FRA1_88321_4' => 'Q63B14 - Niveau Bassin 2 - Q63TM_309 - DT {h} [m]'],
            ];

            $output->writeln('Read file: ' . $file->getFilename());

            foreach ($definedDataSets as $key => $dataSet) {
                $output->writeln(sprintf("Read dataset for Sensor: %s, Parameter: %s", array_keys($dataSet)[0], array_values($dataSet)[0]));
                [$projectName, $sensorName] = explode('_', array_keys($dataSet)[0]);
                $headerCell = array_values($dataSet)[0];
                $parameterName = $this->get_between($headerCell, '{', '}');
                if ($parameterName === false || $parameterName === '') {
                    throw new Exception('Wrong Header Format.');
                }

                $sensor = $this->loadSensor($projectName, $sensorName, $output);
                $parameter = $sensor->getParameterWithName($parameterName);

                if (null === $parameter) {
                    $parameter = Parameter::fromTypeAndName($parameterName, $parameterName);
                    $parameter->setSensor($sensor);
                    $this->em->persist($parameter);
                }

                $alreadyImported = false;
                foreach ($parameter->getDataSets() as $ds) {
                    if ($ds['dataSource'] === DataSource::SOURCE_CSV_SUEZ && $ds['filename'] === $file->getFilename()) {
                        $output->writeln("Already imported!");
                        $alreadyImported = true;
                    }
                }

                if ($alreadyImported) {
                    continue;
                }

                $dateTimeValues = $this->readDataFromFile($csvFile, $key * 2, $key * 2 + 1);
                $dataset = DataSet::fromDatasourceWithData(DataSource::fromCsvSuez(), $dateTimeValues, $file->getFilename());
                $dataset->setParameter($parameter);
                $this->em->persist($dataset);
                $this->em->flush();
            }

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
     * @param int $dateTimeColumn
     * @param int $valueColumn
     * @return DateTimeValue[]
     * @throws Exception
     */
    private function readDataFromFile(string $absoluteFileName, int $dateTimeColumn, int $valueColumn): array
    {
        $handle = fopen($absoluteFileName, 'rb');
        if (!$handle) {
            throw new RuntimeException('Wrong handle!');
        }

        $data = [];
        $lineCounter = 0;
        while (($d = fgetcsv($handle, 10000, ",")) !== FALSE) {
            if ($lineCounter++ < 4) {
                continue;
            }

            // Check if a date is provided
            if (strpos($d[$dateTimeColumn], '/') === false) {
                continue;
            }
            $dateTime = new DateTime(str_replace('/', '-', $d[$dateTimeColumn]));
            $value = (float)str_replace([','], ['.'], $d[$valueColumn]);
            $dtValue = DateTimeValue::fromDateTimeValue($dateTime, $value);
            $data[] = $dtValue;
        }
        fclose($handle);
        return $data;
    }

    private function loadSensor(string $projectName, string $sensorName, OutputInterface $output): Sensor
    {
        $sensor = $this->em->getRepository(Sensor::class)->findOneBy(['project' => $projectName, 'name' => $sensorName]);
        if (null === $sensor) {
            $output->writeln(sprintf("Sensor for Project: %s with Name: %s not found, create a new one.", $projectName, $sensorName));
            $sensor = Sensor::fromProjectNameAndLocation($projectName, $sensorName, '');
        }

        return $sensor;
    }
}
