<?php

namespace Mapbender\ConfiguratorBundle\Component;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BaseComponent
 *
 * @package Mapbender\ConfiguratorBundle\Component
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class BaseComponent implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * Configurator constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        if ($container) {
            $this->setContainer($container);
        }
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param $connectionName
     * @return Connection|mixed
     */
    public function getConnectionByName($connectionName)
    {
        return $this->container->get("doctrine.dbal.{$connectionName}_connection");
    }
}