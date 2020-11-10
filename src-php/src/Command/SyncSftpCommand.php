<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\InvalidFtpCredentialsException;
use phpseclib\Net\SFTP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SyncSftpCommand extends Command
{
    protected static $defaultName = 'app:sync-sftp';

    protected function configure(): void
    {
        $this
            ->setDescription('Syncs all data from a sftp server.')
            ->addArgument('server', InputArgument::REQUIRED)
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('datafolder', InputArgument::OPTIONAL, '', './data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $localDataFolder = __DIR__ . '/../../' . $input->getArgument('datafolder');

        $sftp = new SFTP($input->getArgument('server'));

        if (!$sftp->login($input->getArgument('username'), $input->getArgument('password'))) {
            throw new InvalidFtpCredentialsException('Cannot login into your server !');
        }

        $fileSystem = new Filesystem();
        $fileSystem->mkdir($localDataFolder . $sftp->pwd());

        foreach ($sftp->nlist() as $subFolder) {
            if ($subFolder !== '.' && $subFolder !== '..') {
                $this->fetchSubfolder($sftp, $localDataFolder, $sftp->pwd() . '/' . $subFolder, $output);
            }
        }

        $sftp->disconnect();
        return Command::SUCCESS;
    }

    private function fetchSubfolder(SFTP $sftp, string $localDataFolder, string $subfolder, OutputInterface $output): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($localDataFolder . $subfolder);

        foreach ($sftp->nlist($subfolder) as $file) {
            if ($file !== '.' && $file !== '..') {
                if ($fileSystem->exists($localDataFolder . $subfolder . '/' . $file)) {
                    $output->writeln("File " . $file . " already exists. Skipping.");
                    continue;
                }
                $output->writeln("Downloading file " . $file);
                $sftp->get($subfolder . '/' . $file, $localDataFolder . $subfolder . '/' . $file);
            }
        }
    }

}
