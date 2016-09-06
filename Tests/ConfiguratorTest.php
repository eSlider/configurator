<?php
namespace Mapbender\ConfiguratorBundle\Test;

use Mapbender\ConfiguratorBundle\Component\Configurator;
use Mapbender\ConfiguratorBundle\Entity\Configuration;
use Symfony\Component\DependencyInjection\Container;

/**
 * Test configurator component
 */
class ConfiguratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Configurator */
    protected $configurator;

    public function setUp()
    {
        $container = new Container();
        $container->setParameter("testing", true);
        $this->configurator = new Configurator($container);
        parent::setUp();
    }

    /**
     * Check if configuration table is created
     */
    public function testCreateDatabase()
    {
        $configurator = $this->configurator;
        $db           = $configurator->db();
        $tableName    = $configurator->getTableName();
        $tableInfo    = $db->getTableInfo($tableName);
        $this->assertTrue(count($tableInfo) > 1);
    }

    public function testSaveConfiguration()
    {
        $configurator  = $this->configurator;
        $configuration = new Configuration(array(
            'parentId' => null,
            'key'      => 'application',
            'type'     => 'Mapbender\ConfiguratorBundle\Component\Configurator',
            'children' => array(
                array(
                    'key'   => 'title',
                    'value' => 'Test'
                ),
                array(
                    'key'   => 'description',
                    'value' => 'Test application'
                ),
                array(
                    'key'      => 'elements',
                    'children' => array(
                        array(
                            'key'      => 'element',
                            'type'     => 'MapbenderCoreBundle/MapElement',
                            'children' => array(
                                array(
                                    'key'   => 'container',
                                    'value' => 'Content'
                                ),
                                array(
                                    'key'   => 'width',
                                    'value' => 'auto'
                                ),
                                array(
                                    'key'   => 'height',
                                    'value' => '200px'
                                )
                            )
                        )
                    )
                )
            )
        ));
        $configurator->save($configuration);
    }
}
