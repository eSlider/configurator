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
                'title'  => $trans->trans("Routing"),
                'route'  => 'mapbender_configurator_routing_index',
                'routes' => array(
                    'mapbender_configurator_routing',
                ),
            )
        );
    }
}