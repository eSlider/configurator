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
     * Get database connection handler
     *
     * @return SqliteExtended db Database connection handler
     */
    public function db()
    {
        return $this->db;
    }

    /**
     * Get configuration by id
     *
     * @param        $id       Configuration id
     * @param bool   $children Get children flag
     * @param string $scope
     * @return Configuration
     */
    public function getById($id, $children = true, $scope = null)
    {
        $db     = $this->db();
        $row    = $db->fetchRow("SELECT * FROM config WHERE id=" . intval($id));
        $config = new Configuration($row);
        if ($config->isArray()) {
            $config->setValue(json_decode($config->getValue(), true));
        }
        if ($children) {
            $children1 = $this->getChildren($id, $children, $scope);
            $config->setChildren($children1);
        }
        return $config;
    }

    /**
     * Get by configuration filter
     *
     * @param Configuration $filter
     * @param bool          $children
     * @param null          $scope
     * @return Configuration
     */
    public function get(Configuration $filter, $children = true, $scope = null)
    {
        return $this->getById($filter->getId(), $children, $scope);
    }

    /**
     * Get children
     *
     * @param      $id int
     * @param bool $children
     * @param null $scope
     * @return Configuration[]
     */
    public function getChildren($id, $children = true, $scope = null)
    {
        $db       = $this->db();
        $children = array();
        foreach ($db->queryAndFetch("SELECT id FROM config WHERE parentId=" . intval($id)) as $row) {
            $children[] = $this->getById($row["id"], $children, $scope);
        }
        return $children;
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

        if ($configuration->isArray() || $configuration->isObject()) {
            $data["value"] = json_encode($data["value"]);
        }

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
     * @param $data
     */
    public function saveData($data){

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