<?php
namespace Mapbender\ConfiguratorBundle\Controller;

use FOM\ManagerBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Mapbender application management
 *
 * @Route("configurator/")
 */
class BaseController extends Controller
{
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
