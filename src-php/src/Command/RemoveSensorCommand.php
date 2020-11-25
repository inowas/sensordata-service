<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Sensor;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveSensorCommand extends Command
{
    protected static $defaultName = 'app:rm-sensor';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription('Removes a sensor from DB.');
        $this->addArgument('project', InputArgument::REQUIRED);
        $this->addArgument('sensor', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectName = $input->getArgument('project');
        $sensorName = $input->getArgument('sensor');
        try {
            $sensor = $this->em->getRepository(Sensor::class)->findOneBy(['project' => $projectName, 'name' => $sensorName]);
            if ($sensor instanceof Sensor) {
                $this->em->remove($sensor);
                $this->em->flush();
                return Command::SUCCESS;
            }
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
        }

        return Command::FAILURE;
    }
}
