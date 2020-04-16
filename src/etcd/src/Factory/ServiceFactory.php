<?php

namespace Mix\Etcd\Factory;

use Mix\Etcd\Service\Service;
use Mix\Etcd\Service\ServiceBundle;
use Mix\Grpc\Server as GrpcServer;
use Mix\Http\Server\Server as HttpServer;
use Mix\JsonRpc\Server as JsonRpcServer;
use Mix\Micro\Register\Helper\ServiceHelper;
use Mix\Micro\Register\ServiceFactoryInterface;
use Mix\Micro\Register\ServiceInterface;
use Mix\Route\Router;

/**
 * Class ServiceFactory
 * @package Mix\Etcd\Factory
 */
class ServiceFactory implements ServiceFactoryInterface
{

    /**
     * Create service
     * @param string $name
     * @param string|null $version
     * @return ServiceInterface
     */
    public function createService(string $name, ?string $version = null): ServiceInterface
    {
        return new Service($name, $version);
    }

    /**
     * Create service bundle form api
     * @param HttpServer $server
     * @param Router|null $router
     * @param string $namespace
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromAPI(HttpServer $server, Router $router = null, string $namespace = 'php.micro.api', ?string $version = null)
    {
        $serviceFactory = new ServiceFactory();
        $nodeFactory    = new NodeFactory();
        $services       = [];
        foreach (!is_null($router) ? $router->services() : $server->services() as $name) {
            $service = $serviceFactory->createService(sprintf('%s.%s', $namespace, $name), $version);
            $node    = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'json');
            $node->withMetadata('server', 'json');
            $node->withMetadata('transport', 'http');
            $service->withAddedNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form web
     * @param HttpServer $server
     * @param Router|null $router
     * @param string $namespace
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromWeb(HttpServer $server, Router $router = null, string $namespace = 'php.micro.web', ?string $version = null)
    {
        $serviceFactory = new ServiceFactory();
        $services       = [];
        foreach (!is_null($router) ? $router->services() : $server->services() as $name) {
            $service = $serviceFactory->createService(sprintf('%s.%s', $namespace, $name), $version);
            $node    = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'html');
            $node->withMetadata('server', 'html');
            $node->withMetadata('transport', 'http');
            $service->withAddedNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form json-rpc
     * @param GrpcServer $server
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromGrpc(GrpcServer $server, ?string $version = null)
    {
        $serviceFactory  = new ServiceFactory();
        $endpointFactory = new EndpointFactory();
        $nodeFactory     = new NodeFactory();
        $requestFactory  = new RequestFactory();
        $responseFactory = new ResponseFactory();
        $services        = [];
        foreach ($server->services() as $name => $classes) {
            $service = $serviceFactory->createService($name, $version);
            foreach ($classes as $class) {
                $methods = get_class_methods($class);
                foreach ($methods as $method) {
                    if (strpos($method, '_') === 0) {
                        continue;
                    }

                    if (!$class::NAME) {
                        throw new \InvalidArgumentException(sprintf('Const %s::NAME can\'t be empty', $class));
                    }
                    $slice     = explode('.', $class::NAME);
                    $className = array_pop($slice);
                    $endpoint  = $endpointFactory->createEndpoint(sprintf('%s.%s', $className, $method));

                    $reflectClass     = new \ReflectionClass($class);
                    $reflectMethod    = $reflectClass->getMethod($method);
                    $reflectParameter = $reflectMethod->getParameters()[1];
                    $request          = $requestFactory->createRequest($reflectParameter);
                    $endpoint->withRequest($request);

                    $reflectClass = new \ReflectionClass($reflectMethod->getReturnType()->getName());
                    $response     = $responseFactory->createResponse($reflectClass);
                    $endpoint->withResponse($response);

                    $service->withAddedEndpoint($endpoint);
                }
            }
            $node = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'grpc');
            $node->withMetadata('server', 'grpc');
            $node->withMetadata('transport', 'http');
            $service->withAddedNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form json-rpc
     * @param JsonRpcServer $server
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromJsonRpc(JsonRpcServer $server, ?string $version = null)
    {
        $serviceFactory  = new ServiceFactory();
        $endpointFactory = new EndpointFactory();
        $nodeFactory     = new NodeFactory();
        $services        = [];
        foreach ($server->services() as $name => $classes) {
            $service = $serviceFactory->createService($name, $version);
            foreach ($classes as $class) {
                $methods = get_class_methods($class);
                foreach ($methods as $method) {
                    if (strpos($method, '_') === 0) {
                        continue;
                    }

                    if (!$class::NAME) {
                        throw new \InvalidArgumentException(sprintf('Const %s::NAME can\'t be empty', $class));
                    }
                    $slice     = explode('.', $class::NAME);
                    $className = array_pop($slice);

                    $endpoint = $endpointFactory->createEndpoint(sprintf('%s.%s', $className, $method));
                    $service->withAddedEndpoint($endpoint);
                }
            }
            $node = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'json');
            $node->withMetadata('server', 'json');
            $node->withMetadata('transport', 'tcp');
            $service->withAddedNode($node);
            $services[] = $service;
        }
        return $services;
    }

}
