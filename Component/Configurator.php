<?php

namespace Mapbender\ConfiguratorBundle\Component;

use Eslider\Driver\HKVStorage;
use Eslider\Driver\SqliteExtended;
use Eslider\Entity\HKV;
use Eslider\Entity\HKVSearchFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Configurator
 *
 * @package Mapbender\ConfiguratorBundle\Component
 * @author  Andriy Oblivantsev <eslider@gmail.com>
 */
class Configurator extends BaseComponent
{
    /** @var HKVStorage */
    protected $storage;

    /**
     * Configurator constructor.
     *
     * @param ContainerInterface $container
     * @param string             $path
     * @param string             $tableName
     */
    public function __construct(ContainerInterface $container = null,
        $path = "hkv-storage.db.sqlite",
        $tableName = "key_values")
    {
        $this->storage = new HKVStorage($path, $tableName);
        parent::__construct($container);
    }

    /**
     * Get database connection handler
     *
     * @return SqliteExtended
     */
    public function db()
    {
        return $this->storage->db();
    }

    /**
     * Get by configuration filter
     *
     * @param HKVSearchFilter $filter
     * @return HKV
     */
    public function getByFilter(HKVSearchFilter $filter)
    {
        return $this->getStorage()->get($filter);
    }


    /**
     * @return HKVStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string      $key
     * @param null|string $scope
     * @param null|int    $parentId
     * @param null|int    $userId
     * @return HKV
     */
    public function save($key, $value, $scope = null, $parentId = null, $userId = null)
    {
        return $this->getStorage()->saveData($key, $value, $scope, $parentId, $userId);
    }

    /**
     * Get as data
     *
     * @param string      $key
     * @param null|string $scope
     * @param null|int    $parentId
     * @param null|int    $userId
     * @return HKV
     */
    public function get($key, $scope = null, $parentId = null, $userId = null)
    {
        return $this->getStorage()->getData($key, $scope, $parentId, $userId);
    }

    /**
     * Get Configuration by id
     *
     * @param int  $id            HKV id
     * @return HKV
     */
    public function getById($id)
    {
        $storage = $this->getStorage();
        return $storage::denormalize($storage->getById($id));
    }
}