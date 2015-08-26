<?php

    namespace Dez\ORM\Collection;

    class InvalidArgumentException extends \Exception {}

    class OutOfRangeException extends \Exception {}

    abstract class Collection implements \ArrayAccess, \IteratorAggregate, \Countable {

        protected
            $items      = [],
            $type       = null;

        public function __construct( array $items = [] ) {
            $this->addAll( $items );
        }

        public function getType() {
            return $this->type;
        }

        public function setType( $type = null ) {
            $this->type     = $type;
        }

        public function addAll( array $items ) {
            if( count( $items ) > 0 ) foreach( $items as $item ) {
                $this->add( $item );
            }
        }

        public function at( $index = 0 ) {
            $this->validateIndex( $index );
            return $this->items[$index];
        }

        public function index( $index = 0 ) {
            return $this->at( $index );
        }

        public function isEmpty() {
            return ( $this->count() == 0 );
        }

        public function count() {
            return count( $this->items );
        }

        public function each( callable $callback ) {
            if( $this->count() > 0 )
                foreach( $this->items as $key => $item ) {
                    $callback( $key, $item );
                }
            return $this;
        }

        public function findOne( callable $callback ) {
            $index  = $this->findIndex( $callback );
            return 0 > $index ? false : $this->at( $index );
        }

        /**
         * @param callable $callback
         * @return static
        */

        public function findAll( callable $callback ) {
            $indexes    = $this->findIndexes( $callback );
            $collection = clone $this;
            $collection->removeAll( function() { return true; } );
            foreach ( $indexes as $index ) { $collection->add( $this->at( $index ) ); }
            return $collection;
        }

        public function findIndex( callable $callback ) {
            $index  = -1;
            for( $i = 0, $c = $this->count(); $i < $c; $i++ ) {
                if( $callback( $this->index( $i ) ) ) {
                    $index  = $i; break;
                }
            }
            return $index;
        }

        public function findIndexes( callable $callback ) {
            $indexes  = [];
            for( $i = 0, $c = $this->count(); $i < $c; $i++ ) {
                if( $callback( $this->index( $i ) ) ) {
                    $indexes[]  = $i;
                }
            }
            return $indexes;
        }

        public function removeAll( callable $callback ) {
            $removed    = 0;
            while( $this->remove( $callback ) ) $removed++;
            return $removed;
        }

        public function remove( callable $callback ) {
            $index  = $this->findIndex( $callback );
            if( 0 > $index ) {
                return false;
            } else {
                $this->removeAt( $index ); return true;
            }
        }

        public function removeAt( $index = 0 ) {
            $this->validateIndex( $index );
            $leftPart       = array_slice( $this->items, 0, $index );
            $rightPart      = array_slice( $this->items, $index + 1 );
            $this->items    = array_merge( $leftPart, $rightPart );
        }

        public function clear() { $this->items = []; }

        /**
         * @return static
        */

        public function sort( callable $callback ) {
            usort( $this->items, $callback );
            return $this;
        }

        public function toArray() {
            return $this->items;
        }

        public function toJSON() {
            return json_encode( $this->items );
        }

        protected function validateItem( $item ) {
            if( $this->type != null && ! is_a( $item, $this->type ) ) {
                throw new InvalidArgumentException( 'Collection type must be: '. $this->type .' passed '. gettype( $item ) );
            }
        }

        protected function validateIndex( $index ) {
            if( ! is_int( $index ) ) {
                throw new InvalidArgumentException( 'Index must be integer' );
            }

            if( 0 > $index ) {
                throw new InvalidArgumentException( 'Index must be zero or bigger' );
            }

            if( $this->count() - 1 < $index ) {
                throw new OutOfRangeException( 'Index is out of range. Max index is '. ( $this->count() - 1 ) );
            }
        }

        public function offsetExists( $index ) {
            return isset( $this->items[$index] );
        }

        public function offsetUnset( $index ) {
            $this->removeAt( $index );
        }

        public function offsetGet( $index ) {
            return $this->at( $index );
        }

        public function offsetSet( $index, $item ) {
            if( is_null( $index ) ) {
                $this->items[]          = $item;
            } else {
                $this->items[$index]    = $item;
            }
        }

        public function getIterator() {
            return new \ArrayIterator( $this->items );
        }

        abstract public function add( $item );

    }