<?php

namespace Dez\ORM\Relation;

use Dez\ORM\Collection\ModelCollection;
use Dez\ORM\Common\Object;
use Dez\ORM\Common\SingletonTrait;
use Dez\ORM\Model\Table as TableModel;

abstract class Relation extends Object
{

    use SingletonTrait;

    /**
     * @var TableModel
     */
    protected $model = null;
    /**
     * @var string
     */
    protected $related = null;
    /**
     * @var string
     */
    protected $foreignKey = 'id';
    /**
     * @var string
     */
    protected $fromKey = 'id';
    /**
     * @var array
     */
    protected $ids = [0];

    /**
     * @var ModelCollection
     */
    protected $collection = null;

    /**
     * @param TableModel $model
     * @return $this
     */
    public function setModel(TableModel $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return ModelCollection
     */
    public function get()
    {

        return $this->collection->findAll(function ($item) {
            /** @var TableModel $item */
            $modelValue = $this->fromKey == $this->model->pk()
                ? $this->model->id()
                : $this->model->get($this->fromKey);

            $relatedValue = $this->foreignKey == $item->pk()
                ? $item->id()
                : $item->get($this->foreignKey);

            return $modelValue == $relatedValue;

        });

    }

    /**
     * @param   array $ids
     * @param   TableModel $related
     * @param   string $foreignKey
     * @param $fromKey
     */
    protected function init(array $ids = [], $related, $foreignKey, $fromKey)
    {
        $this->ids = $ids;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->fromKey = $fromKey;
        
        $this->makeRelation();
    }

    /**
     * @return mixed
     */
    abstract protected function makeRelation();

}