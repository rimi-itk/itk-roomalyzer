<?php

/*
 * This file is part of itk-dev/roomalyzer.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\RoomAlyzer\ApiClient;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

class SensorDataCommand extends Command
{
    protected static $defaultName = 'app:sensor:data';

    /** @var ApiClient */
    private $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        parent::__construct();
        $this->apiClient = $apiClient;
    }

    protected function configure()
    {
        $this
            ->addArgument('sensor', InputArgument::REQUIRED, 'Sensor id')
            ->addOption('hours', null, InputOption::VALUE_REQUIRED, 'Hours', 48)
            ->addOption('time', null, InputOption::VALUE_REQUIRED, 'Hours', 'now');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sensorId = $input->getArgument('sensor');
        $hours = (int) $input->getOption('hours');
        $time = new DateTime($input->getOption('time'));

        $client = HttpClient::create();
        $data = $this->apiClient->getSensorData($sensorId, $time, $hours);

        $output->writeln(json_encode($data, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
