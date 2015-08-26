<?php

    namespace Dez\Collection\AbstractCollection;

    class ModelCollection extends AbstractCollection {

        protected
            $keyName = 'id';

        public function setKeyName( $keyName = null ) {
            $this->keyName  = $keyName;
        }

        public function getKeyName() {
            return $this->keyName;
        }

        public function add( $item ) {
            $this->validateItem( $item );
            $this->items[]  = $item;
        }

        public function getIDs() {
            return array_keys( $this->getDictionary() );
        }

        public function getByID( $id ) {
            $dictionary = $this->getDictionary();
            return isset( $dictionary[ $id ] ) ? $dictionary[ $id ] : null;
        }

        public function getDictionary() {
            $dictionary = [];
            foreach( $this->items as $item ) {
                $dictionary[ $this->getKeyName() == 'id' ? $item->id() : $item->get( $this->getKeyName() ) ] = $item;
            }
            return $dictionary;
        }

        public function getPagination() {
            return $this->count() > 0 ? $this->at( 0 )->getPagination() : null;
        }

        public function save() {
            $this->each( function( $i, $item ) { $item->save(); } );
        }

        public function delete() {
            $this->each( function( $i, $item ) { $item->delete(); } );
        }

        public function toArray() {
            $items = [];
            $this->each( function( $i, $item ) use ( & $items ) { $items[] = $item->toArray(); } );
            return $items;
        }

        public function toObject() {
            $items = [];
            $this->each( function( $i, $item ) use ( & $items ) { $items[] = $item->toObject(); } );
            return $items;
        }

        public function toJSON() {
            return json_encode( $this->toArray() );
        }

    }