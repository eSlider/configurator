<?php

namespace Mapbender\ConfiguratorBundle\Component;

use Mapbender\ConfiguratorBundle\Entity\DataItem;
use Mapbender\ConfiguratorBundle\Entity\DataItemSearchFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zumba\Util\JsonSerializer;

/**
 * Class Configurator
 *
 * @package Mapbender\ConfiguratorBundle\Component
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class Configurator extends BaseComponent
{
    const NULL_BYTE = "\1NULL\1";

    /** ID field name */
    const ID_FIELD        = 'id';
    const PARENT_ID_FIELD = 'parentId';

    /** @var string DataItem table name */
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
     * @param int    $id            DataItem id
     * @param bool   $fetchChildren Get children flag
     * @param string $scope
     * @return DataItem
     */
    public function getById($id, $fetchChildren = true, $scope = null)
    {
        $filter = new DataItemSearchFilter();
        $filter->setId($id);
        $filter->setScope($scope);

        if ($fetchChildren) {
            $filter->setFetchMethod(DataItemSearchFilter::FETCH_ONE_AND_CHILDREN);
        } else {
            $filter->setFetchMethod(DataItemSearchFilter::FETCH_ONE_WITHOUT_CHILDREN);
        }

        return $this->get($filter);
    }

    /**
     * Get by configuration filter
     *
     * @param DataItem|DataItemSearchFilter $filter
     * @return DataItem
     */
    public function get(DataItemSearchFilter $filter)
    {
        $db       = $this->db();
        $query    = $this->createQuery($filter);
        $dataItem = new DataItem($db->fetchRow($query));

        if ($dataItem->isArray() || $dataItem->isObject()) {
            $dataItem->setValue($this->decodeValue($dataItem->getValue()));
        }

        if ($filter->shouldFetchChildren()) {
            $children = $this->getChildren($dataItem->getId(), true, $filter->getScope());
            $dataItem->setChildren($children);
        }
        return $dataItem;
    }

    /**
     * Get children
     *
     * @param      $id int
     * @param bool $fetchChildren
     * @param null $scope
     * @return DataItem[]
     */
    public function getChildren($id, $fetchChildren = true, $scope = null)
    {
        $db       = $this->db();
        $children = array();
        $filter   = new DataItemSearchFilter();

        $filter->setParentId($id);
        $filter->setFields(array(self::ID_FIELD));

        foreach ($db->queryAndFetch($this->createQuery($filter)) as $row) {
            $children[] = $this->getById($row[ self::ID_FIELD ], $fetchChildren, $scope);
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
     * @param DataItem $dataItem
     * @param null     $time
     * @return DataItem
     */
    public function save(DataItem $dataItem = null, $time = null)
    {
        $db                  = $this->db();
        $tableName           = $this->getTableName();
        $data                = $dataItem->toArray();

        if (!$time) {
            $time = time();
        }

        $data["creationDate"] = $time;

        if ($dataItem->isArray() || $dataItem->isObject()) {
            $data["value"] = $this->encodeValue($data["value"]);
        }

        unset($data["children"]);

        if ($dataItem->hasId()) {
            $db->update($tableName, $data, $dataItem->getId());
        } else {
            try{
                $id = $db->insert($tableName, $data);
                $dataItem->setId($id);
            } catch (\Exception $e){
                var_dump($e);
            }

        }

        if ($dataItem->hasChildren()) {
            foreach ($dataItem->getChildren() as $child) {
                $child->setParentId($dataItem->getId());
                $this->save($child, $time);
            }
        }

        return $dataItem;
    }

    /**
     * @param      $key
     * @param      $value
     * @param null $parentId
     * @param null $scope
     * @internal param $data
     * @return DataItem
     */
    public function saveData($key, $value, $scope = null, $parentId = null)
    {
        $dataItem = new DataItem();
        $isArray  = is_array($value);
        $dataItem->setKey($key);
        $dataItem->setParentId($parentId);
        $dataItem->setScope($scope);
        $dataItem->setType(gettype($value));

        if (!$isArray) {
            $dataItem->setValue($value);
        }

        $this->save($dataItem);

        if ($isArray) {
            $childParentId = $dataItem->getId();
            $children      = array();
            foreach ($value as $subKey => $item) {
                $children[] = $this->saveData($subKey, $item, $scope, $childParentId);
            }
            $dataItem->setChildren($children);
        }

        return $dataItem;
    }

    /**
     * Get field names
     *
     * @return array
     */
    protected function getFieldNames()
    {
        //$configurationVars    = new \ReflectionClass('Mapbender\ConfiguratorBundle\Entity\DataItem');
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

    /**
     * @param      $key
     * @param null $scope
     * @param null $parentId
     * @param null $userId
     * @return DataItem
     */
    public function restoreData($key, $scope = null, $parentId = null, $userId = null)
    {
        $filter = new DataItemSearchFilter();
        $filter->setKey($key);
        $filter->setParentId($parentId);
        $filter->setScope($scope);
        $filter->setUserId($userId);
        $dataItem = $this->get($filter);

        $data = $this->normalizeObject($dataItem);

        return $data;
    }

    /**
     * @param DataItem $dataItem
     * @return null|array|mixed
     */
    public function normalizeObject(DataItem $dataItem)
    {
        $result = null;
        if ($dataItem->getType() == 'array') {
            $result = array();
            foreach ($dataItem->getChildren() as $child) {
                if ($child->getType() == 'array') {
                    $result[ $child->getKey() ] = $this->normalizeObject($child);
                } else {
                    $result[ $child->getKey() ] = $child->getValue();
                }
            }
        } else {
            $result = $dataItem->getValue();
        }
        return $result;
    }

    /**
     * @param $value
     * @return string
     */
    protected function encodeValue($value)
    {
        $serializer = new JsonSerializer();
        return $serializer->serialize($value);
        //return str_replace("\0", self::NULL_BYTE, serialize($value));
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function decodeValue($value)
    {
        $serializer = new JsonSerializer();
        return $serializer->unserialize($value);
        //return unserialize(str_replace(self::NULL_BYTE, "\0", $value));
    }

    /**
     * @param DataItemSearchFilter $filter
     * @return array
     */
    protected function createQuery(DataItemSearchFilter $filter)
    {
        $db     = $this->db();
        $sql    = array();
        $where  = array();
        $fields = $filter->getFields();
        $sql[]  = 'SELECT ' . ($fields ? implode(',', $fields) : '*');
        $sql[]  = 'FROM ' . $db->quote($this->tableName);

        if ($filter->hasId()) {
            $where[] = static::ID_FIELD . '=' . intval($filter->getId());
        }elseif ($filter->getKey()) {
            $where[] = 'key LIKE ' . $db::escapeValue($filter->getKey());
        }

        if ($filter->getParentId()) {
            $where[] = static::PARENT_ID_FIELD . '=' . intval($filter->getParentId());
        }

        if($filter->getScope()){
            $where[] = 'scope LIKE ' . $db::escapeValue($filter->getScope());
        }else{
            $where[] = 'scope IS NULL';
        }

        if ($filter->getType()) {
            $where[] = 'type LIKE ' . $db::escapeValue($filter->getType());
        }


        $sql[] = 'WHERE ' . implode(' AND ', $where);
        $sql[] = 'GROUP BY ' . $db->quote('key');
        $sql[] = 'ORDER BY creationDate DESC';

        if ($filter->hasLimit()) {
            $sql[] = 'LIMIT ' . $filter->getFetchLimit();
        }

        return implode(' ', $sql);
    }
}