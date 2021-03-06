<?php

namespace Mix\Micro;

use Mix\Micro\Config\ConfigInterface;
use Mix\Micro\Register\RegistryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Micro
 * @package Mix\Micro
 */
class Micro
{

    /**
     * New Service
     * @param \Closure ...$options
     * @return Service
     */
    public static function service(\Closure ...$options): Service
    {
        return new Service(...$options);
    }

    /**
     * Name
     * @param string $name
     * @return \Closure
     */
    public static function name(string $name)
    {
        return function (Options $options) use ($name) {
            $options->name = $name;
        };
    }

    /**
     * Version
     * @param string $name
     * @return \Closure
     */
    public static function version(string $version)
    {
        return function (Options $options) use ($version) {
            $options->version = $version;
        };
    }

    /**
     * Metadata
     * @param array $metadata
     * @return \Closure
     */
    public static function metadata(array $metadata)
    {
        return function (Options $options) use ($metadata) {
            $options->metadata = $metadata;
        };
    }

    /**
     * Logger
     * @param LoggerInterface $logger
     * @return \Closure
     */
    public static function logger(LoggerInterface $logger)
    {
        return function (Options $options) use ($logger) {
            $options->logger = $logger;
        };
    }

    /**
     * Registry
     * @param RegistryInterface $registry
     * @return \Closure
     */
    public static function registry(RegistryInterface $registry)
    {
        return function (Options $options) use ($registry) {
            $options->registry = $registry;
        };
    }

    /**
     * Config
     * @param ConfigInterface $config
     * @return \Closure
     */
    public static function config(ConfigInterface $config)
    {
        return function (Options $options) use ($config) {
            $options->config = $config;
        };
    }

    /**
     * Server
     * @param \Mix\Http\Server\Server|\Mix\Grpc\Server|\Mix\JsonRpc\Server $server
     * @return \Closure
     * @throws \InvalidArgumentException
     */
    public static function server($server)
    {
        if (!($server instanceof \Mix\Http\Server\Server) && !($server instanceof \Mix\Grpc\Server) && !($server instanceof \Mix\JsonRpc\Server)) {
            throw new \InvalidArgumentException('$server type is not supported');
        }
        return function (Options $options) use ($server) {
            $options->server = $server;
        };
    }

    /**
     * Router
     * @param \Mix\Micro\Route\Router $router
     * @return \Closure
     */
    public static function router(\Mix\Micro\Route\Router $router)
    {
        return function (Options $options) use ($router) {
            $options->router = $router;
        };
    }

    /**
     * Signal
     * @param bool $open
     * @return \Closure
     */
    public static function signal(bool $open)
    {
        return function (Options $options) use ($open) {
            $options->signal = $open;
        };
    }

}
