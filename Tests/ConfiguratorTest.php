<?php
namespace Mapbender\ConfiguratorBundle\Test;

use Eslider\Driver\SqliteExtended;
use Eslider\Entity\HKV;
use Mapbender\ConfiguratorBundle\Component\Configurator;
use Symfony\Component\DependencyInjection\Container;

/**
 * Test configurator component
 */
class ConfiguratorTest extends \PHPUnit_Framework_TestCase
{
    protected $testAppConfiguration;

    /** @var Configurator */
    protected $configurator;

    /** @var HKV */
    protected static $dataItem;

    public function setUp()
    {
        $container = new Container();
        $container->setParameter("testing", true);
        $this->configurator = new Configurator($container, "hkv.db.sqlite");
        $this->testAppConfiguration = array(
            'parentId' => null,
            'key'      => 'application',
            'value'    => array(
                'obj'   => $this->configurator,
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
                    'key'   => 'component',
                    'value' => $this->configurator
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
        );

        parent::setUp();
    }

    /**
     * Check if configuration table is created
     */
    public function testCreateDatabase()
    {
        $configurator = $this->configurator;
        $db        = $configurator->db();
        $tableName = $configurator->getStorage()->getTableName();
        $tableInfo = $db->getTableInfo($tableName);
        $this->assertTrue(count($tableInfo) > 1);
    }

    /**
     * Test save configuration
     */
    public function testSaveConfiguration()
    {
        $configurator = $this->configurator;
        self::$dataItem = $configurator->save('newApp',$this->testAppConfiguration);
    }

    /**
     * Test retrieve configuration
     */
    public function testGetConfiguration()
    {
        $configurator = $this->configurator;
        $restored     = $configurator->getById(self::$dataItem->getId());
        $this->assertEquals($this->testAppConfiguration, $restored);
    }

    public function testSaveArray()
    {
        /** @var HKV $dataItem */
        $configurator  = $this->configurator;
        $testKey       = 'testSaveArray';
        $testArray     = array('test'         => 'xxx',
                               'configuRATor' => $configurator,
                               'someThing'    => array(
                                   'roles' => array(
                                       'xxx',
                                       'ddd'
                                   )
                               )
        );
        $dataItem      = $configurator->save($testKey, $testArray);
        $restoredArray = $configurator->get($testKey);
        $this->assertEquals($testArray, $restoredArray);
        $this->assertTrue($dataItem->hasId());
    }

}
