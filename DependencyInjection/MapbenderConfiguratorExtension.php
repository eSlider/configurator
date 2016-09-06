<?php
namespace Mapbender\ConfiguratorBundle\DependencyInjection;

use Mapbender\DataSourceBundle\Extension\BaseXmlLoaderExtension;

/**
 * Class MapbenderConfiguratorExtension
 *
 * @package Mapbender\ConfiguratorBundle\DependencyInjection
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class MapbenderConfiguratorExtension extends BaseXmlLoaderExtension
{
    /**
     * Load configuration.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controllers.xml');

        if (isset($config['serializer'])) {
            $container->setAlias('fos_js_routing.serializer', new Alias($config['serializer'], false));
        } else {
            $loader->load('serializer.xml');
        }

        $container
            ->getDefinition('fos_js_routing.extractor')
            ->replaceArgument(1, $config['routes_to_expose']);

        if (isset($config['request_context_base_url'])) {
            $container->setParameter('fos_js_routing.request_context_base_url', $config['request_context_base_url']);
        }

        if (isset($config['cache_control'])) {
            $config['cache_control']['enabled'] = true;
        } else {
            $config['cache_control'] = array('enabled' => false);
        }

        $container->setParameter('fos_js_routing.cache_control', $config['cache_control']);
    }
}