<?php
namespace Mapbender\ConfiguratorBundle\Component;

use Eslider\Driver\HKVStorage;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Routing
 *
 * @package Mapbender\ConfiguratorBundle\Component
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class Routing extends BaseComponent
{
    /**
     * @return array
     */
    protected function getRoutingYamlPath()
    {
        return $this->getConfigurationPath() . "/routing.yml";
    }

    /**
     * @return array
     * @internal param $routingPath
     */
    protected function getCurrentRoutes()
    {
        $routingPath = $this->getRoutingYamlPath();
        $routing     = Yaml::parse($routingPath);
        $list        = array();
        foreach ($routing as $id => $route) {
            $route["id"]      = $id;
            $route["enabled"] = true;
            $list[ $id ]      = $route;
        }
        return $list;
    }


    /**
     * @return array
     */
    protected function getPossibleDynamicRoutes()
    {
        /** @var SplFileInfo $file */
        $finder     = new Finder();
        $kernel     = $this->container->get("kernel");
        $routesPath = realpath($kernel->getRootDir() . "/../vendor/mapbender");
        $files      = array();
        foreach ($finder->files()->in($routesPath)->name("*Bundle.php") as $file) {
            $fileName = $file->getFilename();
            preg_match_all("/[A-Z0-9][a-z0-9]+/s", $fileName, $matches);
            $matches    = current($matches);
            $vendorName = current($matches);
            $bundleName = implode(null, array_splice($matches, 1, count($matches) - 1));
            $id         = 'mapbender_' . strtolower($bundleName);
            $resource   = '@' . $vendorName . $bundleName . '/Controller/';

            try {
                $kernel->locateResource($resource);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $files[ $id ] = array(
                'enabled'  => false,
                'id'       => $id,
                'type'     => 'annotation',
                'resource' => $resource
            );
        }
        return $files;
    }

    /**
     * @return array
     */
    public function getAllRoutes()
    {
        return array_merge(
            $this->getPossibleDynamicRoutes(),
            $this->getCurrentRoutes()
        );
    }

    /**
     * @param $routes
     * @return int
     */
    public function save($routes)
    {
        $routeConfigDbPath = $this->getConfigurationPath() . "/routing.sqlite";
        $isNew             = is_file($routeConfigDbPath);
        if ($isNew) {
            $oldRoutingStorage = new HKVStorage($routeConfigDbPath, "old_routes");
            $oldRoutingStorage->saveData('collection', $routes);
        }

        return file_put_contents(
            $this->getRoutingYamlPath(),
            Yaml::dump($routes)
        );
    }

    /**
     * @return string
     */
    protected function getConfigurationPath()
    {
        $kernel     = $this->container->get("kernel");
        $configPath = $kernel->getRootDir() . "/config";
        return $configPath;
    }
}