<?php

namespace Mix\Micro\Etcd\Register;

use Mix\Micro\Etcd\Client\Client;
use Mix\Concurrent\Timer;
use Mix\Micro\Etcd\Service\Service;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Register\ServiceInterface;

/**
 * Class Registrar
 * @package Mix\Micro\Etcd\Register
 */
class Registrar
{

    /**
     * @var Client
     */
    public $client;

    /**
     * @var Service
     */
    public $service;

    /**
     * @var int
     */
    public $ttl;

    /**
     * @var int
     */
    protected $leaseID;

    /**
     * @var Timer
     */
    protected $timer;

    /**
     * @var string
     */
    protected $serviceFormat = '%s/%s/%s';

    /**
     * Registrar constructor.
     * @param Client $client
     * @param Service $service
     * @param string $namespace
     * @param int $ttl
     */
    public function __construct(Client $client, Service $service, string $namespace, int $ttl)
    {
        $this->client        = $client;
        $this->service       = $service;
        $this->serviceFormat = sprintf($this->serviceFormat, $namespace, '%s', '%s');
        $this->ttl           = $ttl;
    }

    /**
     * Register
     * @throws \Exception
     */
    public function register()
    {
        $client  = $this->client;
        $service = $this->service;
        $reslut  = $client->grant($this->ttl);
        $leaseID = $this->leaseID = (int)$reslut['ID'];
        $client->put(sprintf($this->serviceFormat, $service->getName(), $service->getID()), json_encode($service, JSON_UNESCAPED_SLASHES), ['lease' => $leaseID]);
        $this->timer and $this->timer->clear();
        $this->timer = $this->keepAlive();
    }

    /**
     * Un Register
     */
    public function deregister()
    {
        $this->timer->clear();
        try {   // 忽略异常
            $this->client->revoke($this->leaseID);
        } catch (\Throwable $throwable) {
        }
    }

    /**
     * Keep alive
     * @return Timer
     */
    protected function keepAlive()
    {
        $timer = Timer::new();
        $timer->tick($this->ttl * 1000 / 5 * 4, function () {
            try {
                // 当 lease 失效时，由于返回结果缺少 ttl，所以会抛出异常
                $this->client->keepAlive($this->leaseID);
            } catch (NotFoundException $ex) {
                $this->register();
            }
        });
        return $timer;
    }

}
