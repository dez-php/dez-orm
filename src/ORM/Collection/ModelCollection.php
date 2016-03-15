<?php

namespace Dez\ORM\Collection;

use Dez\Collection\AbstractCollection;
use Dez\ORM\Common\Pagi;
use Dez\ORM\Model\Table;

/**
 * Class ModelCollection
 * @package Dez\ORM\Collection
 */
class ModelCollection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $keyName = 'id';

    /**
     * @param $item
     * @return $this
     * @throws \Dez\Collection\InvalidArgumentException
     */
    public function add($item)
    {
        $this->validateItem($item);
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param $item
     * @return $this
     * @throws \Dez\Collection\InvalidArgumentException
     */
    public function append($item)
    {
        $this->validateItem($item);

        $this->items[] = $item;

        return $this;
    }

    /**
     * @param $item
     * @return $this
     * @throws \Dez\Collection\InvalidArgumentException
     */
    public function prepend($item)
    {
        $this->validateItem($item);

        array_unshift($this->items, $item);

        return $this;
    }

    /**
     * @param null $key
     * @return array
     */
    public function getIDs($key = null)
    {
        return array_keys($this->getDictionary($key));
    }

    /**
     * @param null $key
     * @return array
     */
    public function getDictionary($key = null)
    {
        $dictionary = [];
        $key = !$key ? $this->getKeyName() : $key;

        foreach ($this->items as $item) {
            /** @var Table $item */
            $dictionary[$key == 'id' ? $item->id() : $item->get($key)] = $item;
        }

        return $dictionary;
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param null $keyName
     * @return $this
     */
    public function setKeyName($keyName = null)
    {
        $this->keyName = $keyName;

        return $this;
    }

    /**
     * @param $id
     * @return null
     */
    public function getByID($id)
    {
        $dictionary = $this->getDictionary();

        return isset($dictionary[$id]) ? $dictionary[$id] : null;
    }

    /**
     * @return Pagi
     */
    public function getPagination()
    {
        return $this->count() > 0 ? $this->at(0)->getPagination() : null;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $this->each(function ($i, $item) {
            /** @var Table $item */
            $item->save();
        });

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $this->each(function ($i, $item) {
            /** @var Table $item */
            $item->delete();
        });

        return $this;
    }

    /**
     * @return array
     */
    public function toObject()
    {
        $items = [];
        
        $this->each(function ($i, $item) use (& $items) {
            /** @var Table $item */
            $items[] = $item->toObject();
        });

        return $items;
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $items = [];

        $this->each(function ($i, $item) use (& $items) {
            /** @var Table $item */
            $items[] = $item->toArray();
        });

        return $items;
    }

}