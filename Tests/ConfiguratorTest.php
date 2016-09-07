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
    /** @var Configurator */
    protected $configurator;
    /** @var Configuration */
    protected static $configuration;

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

    /**
     * Test save configuration
     */
    public function testSaveConfiguration()
    {
        $configurator  = $this->configurator;
        $configuration = new Configuration(array(
            'parentId' => null,
            'key'      => 'application',
            'value'    => array(
                'roles' => array(
                    'read'  => array('test1', 'test2'),
                    'write' => array('test1', 'test2'),
                ),
            ),
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
                        ),
                        array(
                            'key'      => 'element',
                            'type'     => 'MapbenderCoreBundle/Digitizer',
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
        self::$configuration = $configuration;
    }

    /**
     * Test retrieve configuration
     */
    public function testGetConfiguration()
    {
        $configurator  = $this->configurator;
        $configuration = self::$configuration;
        $id            = $configuration->getId();
        $restored      = $configurator->getById($id);

        $this->assertEquals($id, $restored->getId());
        $this->assertEquals(count($restored->getChildren()), count($configuration->getChildren()));
    }
}
