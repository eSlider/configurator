<?php
namespace Mapbender\ConfiguratorBundle\Controller;

use Eslider\Driver\HKVStorage;
use FOM\ManagerBundle\Configuration\Route;
use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use Mapbender\ConfiguratorBundle\Component\Routing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;

/**
 * Mapbender application management
 *
 * @Route("routing/")
 */
class RoutingController extends BaseController
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
            'title' => 'Routes',
        );
    }

    /**
     * Get all routes
     *
     * @ManagerRoute("list")
     */
    public function listAction()
    {
        $projectRootPath = realpath($this->get('kernel')->getRootDir()."/../");
        echo `${projectRootPath}/bin/composer clean`;
        $routing = new Routing($this->container);
        $routes  = $routing->getAllRoutes();
        $result  = array(
            'list'  => array_values($routes),
            'title' => 'Repository',
        );
        return new JsonResponse($result);
    }

    /**
     * Save  the layer service repository.
     *
     * @ManagerRoute("save")
     */
    public function saveAction()
    {
        $routing       = new Routing($this->container);
        $request       = $this->getRequestData();
        $allRoutes     = $routing->getAllRoutes();
        $enabledRoutes = array();
        $strictRoutes  = array(
            'mapbender_start',
            'mapbender_corebundle',
            'mapbender_managerbundle',
            'fom_managerbundle',
            'fom_userbundle',
            'fos_js_routing',
            'ows_corebundle',
            'mapbender_configuratorbundle'
        );

        // Filter enabled routes
        foreach ($request["list"] as $routeId) {
            foreach ($allRoutes as $id => $route) {
                if ($id == $routeId || in_array($id, $strictRoutes)) {
                    unset($route["enabled"]);
                    unset($route["id"]);
                    $enabledRoutes[ $id ] = $route;
                }
            }
        }

        $routing->save($enabledRoutes);
        return new JsonResponse($enabledRoutes);
    }
}
