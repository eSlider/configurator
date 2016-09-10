<?php
namespace Mapbender\ConfiguratorBundle\Entity;


/**
 * Base configuration entity
 *
 * @see Documents/API.puml
 */
class DataItem extends BaseEntity
{
    /** @var string String type */
    const TYPE_ARRAY  = "array";

    /** @var string Array type */
    const TYPE_OBJECT = "object";

    /** @var string Array type */
    const TYPE_STRING = "string";

    /** @var  int ID */
    protected $id;

    /** @var  int Parent ID */
    protected $parentId;

    /** @var  string Key name */
    protected $key;

    /** @var  string Key name */
    protected $type;

    /** @var mixed Value */
    protected $value;

    /** @var DataItem[] */
    protected $children;

    /** @var string Scope name */
    protected $scope;

    /** @var \DateTime Creation date */
    protected $creationDate;

    /** @var mixed Value */
    protected $userId;

    /**
     * BaseEntity constructor.
     *
     * @param array $data
     * @param bool  $saveOriginalData Save testing friendly original data as array?.
     */
    public function __construct(array $data = null)
    {
        parent::__construct($data);
    }

    /**
     * @param int|null $id
     * @return DataItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function hasId()
    {
        return $this->getId() !== null;
    }

    /**
     * @param int|null $parentId
     * @return DataItem
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param string|null $scope
     * @return DataItem
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return DataItem[]|null
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set children
     *
     * @param DataItem[] $children
     * @return $this
     */
    public function setChildren(array $children)
    {
        $_children = array();
        foreach ($children as $child) {
            if (is_array($child)) {
                $_children[] = new DataItem($child);
            } elseif (is_object($child) && $child instanceof DataItem) {
                $_children[] = $child;
            }
        }
        $this->children = $_children;
        return $this;
    }

    /**
     * @param mixed $userId
     * @return DataItem
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param \DateTime $creationDate
     * @return DataItem
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param string $type
     * @return DataItem
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        $type = $this->type;

        if (!$type) {

            $value = $this->getValue();

            if($value === null){
                $type = null;
            }elseif (is_object($value)) {
                $type = get_class($value);
            } else {
                $type = gettype($value);
            }
            $this->type = $type;
        }

        return $type;
    }

    /**
     * Export class variables
     *
     * @return array
     */
    public function toArray()
    {
        $vars = get_object_vars($this);

        unset($vars["_data"]);
        unset($vars["saveOriginalData"]);

        if ($this->hasChildren()) {
            $children = array();
            foreach ($this->getChildren() as $child) {
                $children[] = $child->toArray();
            }
            $vars["children"] = $children;
        }
        $vars["type"] = $this->getType();

        return $vars;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param mixed $value
     * @return DataItem
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isArray()
    {
        return $this->getType() == static::TYPE_ARRAY;
    }

    /**
     * @return bool
     */
    public function isObject()
    {
        $type = $this->getType();
        return $type == static::TYPE_OBJECT || strpos($type, "\\");
    }
}