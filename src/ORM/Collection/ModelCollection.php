<?php

    namespace Dez\ORM\Collection;

    use Dez\Collection\AbstractCollection;

    /**
     * Class ModelCollection
     * @package Dez\ORM\Collection
     */
    class ModelCollection extends AbstractCollection {

        /**
         * @var string
         */
        protected
            $keyName = 'id';

        /**
         * @param null $keyName
         */
        public function setKeyName( $keyName = null ) {
            $this->keyName  = $keyName;
            return $this;
        }

        /**
         * @return string
         */
        public function getKeyName() {
            return $this->keyName;
        }

        /**
         * @param $item
         * @throws \Dez\Collection\InvalidArgumentException
         */
        public function add( $item ) {
            $this->validateItem( $item );
            $this->items[]  = $item;
        }

        /**
         * @return array
         */
        public function getIDs( $key = null ) {
            return array_keys( $this->getDictionary( $key ) );
        }

        /**
         * @param $id
         * @return null
         */
        public function getByID( $id ) {
            $dictionary = $this->getDictionary();
            return isset( $dictionary[ $id ] ) ? $dictionary[ $id ] : null;
        }

        /**
         * @return array
         */
        public function getDictionary( $key = null ) {
            $dictionary = [];
            $key        = ! $key ? $this->getKeyName() : $key;
            foreach( $this->items as $item ) {
                $dictionary[ $key == 'id' ? $item->id() : $item->get( $key ) ] = $item;
            }
            return $dictionary;
        }

        /**
         * @return null
         */
        public function getPagination() {
            return $this->count() > 0 ? $this->at( 0 )->getPagination() : null;
        }

        /**
         *
         */
        public function save() {
            $this->each( function( $i, $item ) { $item->save(); } );
        }

        /**
         *
         */
        public function delete() {
            $this->each( function( $i, $item ) { $item->delete(); } );
        }

        /**
         * @return array
         */
        public function toArray() {
            $items = [];
            $this->each( function( $i, $item ) use ( & $items ) { $items[] = $item->toArray(); } );
            return $items;
        }

        /**
         * @return array
         */
        public function toObject() {
            $items = [];
            $this->each( function( $i, $item ) use ( & $items ) { $items[] = $item->toObject(); } );
            return $items;
        }

        /**
         * @return string
         */
        public function toJSON() {
            return json_encode( $this->toArray() );
        }

    }