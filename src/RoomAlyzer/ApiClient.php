<?php

/*
 * This file is part of itk-dev/roomalyzer.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\RoomAlyzer;

use DateTime;
use DateTimeInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * RoomAlyzer api client.
 *
 * @see https://app.roomalyzer.com/index.php?w2d=notification
 */
class ApiClient
{
    /** @var array */
    private $options;

    /** @var HttpClient */
    private $client;

    /** @var string */
    private $algo = 'snefru256';

    public function __construct(array $roomAlyzerClientOptions)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($roomAlyzerClientOptions);
    }

    public function getSensorList(): ?array
    {
        return $this->request('GET', '', [
            'lane' => 'sensor_list',
            'account' => $this->options['account'],
        ]);
    }

    public function getSensorData(string $sensorId, DateTimeInterface $time, int $hours = 48): ?array
    {
        return $this->request('GET', '', [
            'lane' => 'sensor_data',
            'sensor' => $sensorId,
            'time' => $time,
            'hours' => $hours,
        ]);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'url' => 'https://app.roomalyzer.com/api/index.php',
        ])
        ->setRequired(['account', 'token']);
    }

    /**
     * @return array
     *
     * @throws \JsonException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function request(string $method, string $path, array $query = []): ?array
    {
        if (null === $this->client) {
            $this->client = HttpClient::create([
                'base_uri' => $this->options['url'],
            ]);
        }

        $time = ($query['time'] ?? new DateTime())->getTimestamp();
        $token = $this->options['token'];
        $checksum = hash($this->algo, implode('.', [
            $time,
            $query['sensor'] ?? $this->options['account'],
            $token,
        ]));

        $query['time'] = $time;
        $query['checksum'] = $checksum;

        $options = [
            'query' => $query,
        ];

        $result = $this->client->request($method, $path, $options);
        $data = json_decode($result->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return ('success' === $data['status'] ?? null) ? $data['data'] : null;
    }
}
