<?php

namespace Mapbender\ConfiguratorBundle\Component;

use Mapbender\ConfiguratorBundle\Entity\Configuration;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Configurator
 *
 * @package Mapbender\ConfiguratorBundle\Component
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class Configurator extends BaseComponent
{
    /** @var string Configuration table name */
    protected $tableName = "config";

    /** @var SqliteExtended SQLite driver connection */
    protected $db;

    /**
     * Configurator constructor.
     *
     * @param ContainerInterface $container
     * @param string             $path
     */
    public function __construct(ContainerInterface $container = null, $path = "configuration.db.sqlite")
    {
        $emptyDatabase = !file_exists($path);
        $this->db      = new SqliteExtended($path);

        if ($emptyDatabase) {
            $this->createDbStructure();
        }
        parent::__construct($container);
    }

    /**
     * @param        $id
     * @param string $scope
     * @return Configuration
     */
    public function getById($id, $scope = 'global')
    {
        return new Configuration();
    }

    /**
     * Get by configuration filter
     *
     * @param Configuration $filter
     * @return Configuration[]
     *
     */
    public function get(Configuration $filter)
    {
        return array($filter);
    }

    /**
     * @param $id int
     */
    public function getChildren($id)
    {

    }

    /**
     * @return SqliteExtended db
     */
    public function db()
    {
        return $this->db;
    }

    /**
     * Create database4 file
     */
    protected function createDbStructure()
    {
        $db        = $this->db();
        $tableName = $this->tableName;
        if (!$db->hasTable($tableName)) {
            $db->createTable($tableName);
            $fieldNames = $this->getFieldNames();
            foreach ($fieldNames as $fieldName) {
                $db->addColumn($tableName, $fieldName);
            }
        }
    }

    /**
     * Remove database file
     */
    public function destroy()
    {
        $this->db()->destroy();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Save configuration
     *
     * @param Configuration $configuration
     * @return Configuration
     */
    public function save(Configuration $configuration = null)
    {
        $db                  = $this->db();
        $tableName           = $this->getTableName();
        $data                = $configuration->toArray();
        $data["creationDate"] = time();

        unset($data["children"]);

        if ($configuration->hasId()) {
            $db->update($tableName, $data, $configuration->getId());
        } else {
            $id = $db->insert($tableName, $data);
            $configuration->setId($id);
        }

        if ($configuration->hasChildren()) {
            foreach ($configuration->getChildren() as $child) {
                $child->setParentId($configuration->getId());
                $this->save($child);
            }
        }

        return $configuration;
    }

    /**
     * Get field names
     *
     * @return array
     */
    protected function getFieldNames()
    {
        //$configurationVars    = new \ReflectionClass('Mapbender\ConfiguratorBundle\Entity\Configuration');
        //$reflectionProperties = $configurationVars->getProperties();
        //$excludeFields        = array("id", "_data", "saveOriginalData", "children");
        //$fieldNames           = array();
        //foreach ($reflectionProperties as $property) {
        //    $fieldName = $property->getName();
        //    if (in_array($fieldName, $excludeFields)) {
        //        continue;
        //    }
        //    $fieldNames[] = $fieldName;
        //}
        //return $fieldNames;
        return array(
            'parentId',
            'key',
            'type',
            'value',
            'scope',
            'creationDate',
            'userId'
        );
    }
}