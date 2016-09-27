<?php
namespace Mapbender\ConfiguratorBundle;

use Mapbender\CoreBundle\Component\MapbenderBundle;

/**
 * DataSource Bundle.
 * y
 *
 * @author Andriy Oblivantsev
 */
class MapbenderConfiguratorBundle extends MapbenderBundle
{
    /**
     * @inheritdoc
     */
    public function getManagerControllers()
    {
        $trans = $this->container->get('translator');
        return array(
            array(
                'weight' => 20,
                'title'  => "FeatureTypes",
                'route'  => 'mapbender_configurator_configurator_index',
                'routes' => array(
                    'mapbender_configurator_configurator',
                ),
            ),
            array(
                'weight' => 20,
                'title'  => "Routing",
                'route'  => 'mapbender_configurator_routing_index',
                'routes' => array(
                    'mapbender_configurator_routing',
                ),
            )
        );
    }
}