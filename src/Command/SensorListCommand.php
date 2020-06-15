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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

class SensorListCommand extends Command
{
    protected static $defaultName = 'app:sensor:list';

    /** @var ApiClient */
    private $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        parent::__construct();
        $this->apiClient = $apiClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = HttpClient::create();
        $sensors = $this->apiClient->getSensorList();

        $headers = [
            'id',
            'name',
            'location',
            'created',
        ];
        $rows = array_map(static function (array $sensor) {
            return [
                $sensor['hardware_id'],
                $sensor['trap_name'],
                $sensor['location'],
                $sensor['created'],
            ];
        }, $sensors);
        $io->table($headers, $rows);

        return self::SUCCESS;
    }
}
