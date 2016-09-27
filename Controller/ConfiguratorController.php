<?php
namespace Mapbender\ConfiguratorBundle\Controller;

use Eslider\Driver\HKVStorage;
use FOM\ManagerBundle\Configuration\Route;
use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;

/**
 * Mapbender application management
 *
 * @Route("configurator/")
 */
class ConfiguratorController extends BaseController
{
    /**
     * Renders the layer service repository.
     *
     * @ManagerRoute("{page}", defaults={ "page"=1 }, requirements={ "page"="\d+" })
     * @Method({ "GET" })
     * @Template
     */
    public function indexAction($page)
    {
        return array(
            'title' => 'Konfigurator',
        );
    }

    /**
     * Renders the layer service repository.
     *
     * @ManagerRoute("list")
     */
    public function listAction()
    {
        $request        = $this->getRequestData();
        $kernel         = $this->container->get("kernel");
        $configPath     = $kernel->getRootDir() . "/config";
        $routingPath    = $configPath . "/routing.yml";
        $routing        = Yaml::parse($routingPath);
        $routingStorage = new HKVStorage($configPath . "/routing.sqlite", "routing");

        $routingStorage->saveData('collection', $routing);

        return new JsonResponse(array(
            'routing' => $routing,
            'title'   => 'Repository',
            'request' => $request,
            'path'    => $configPath
        ));
    }

}
