<?php
namespace Mapbender\ConfiguratorBundle\Controller;

use Eslider\Driver\HKVStorage;
use FOM\ManagerBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;

/**
 * Mapbender application management
 *
 * @Route("configurator/")
 */
class ConfiguratorController extends Controller
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
            'title' => 'Repository',
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

    /**
     * Get and optional decode JSON request data
     *
     * @return mixed
     */
    protected function getRequestData()
    {
        $content = $this->getRequest()->getContent();
        $request = array_merge($_POST, $_GET);
        if (!empty($content)) {
            $request = array_merge($request, json_decode($content, true));
        }
        return $request;
    }
}
