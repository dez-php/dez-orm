<?php

    namespace Dez\ORM\Common;

    use Dez\ORM\Exception as ORMException;

    class Event {

        use SingletonTrait;

        protected
            $callbacks = [];

        protected function init() {}

        public function attach( $name = null, $callback = null ) {
            if( ! $name || ! $callback )
                throw new ORMException( __METHOD__ .' [ bad attach callback function ]' );

            if( ! isset( $this->callbacks[$name] ) || ! is_array( $this->callbacks[$name] ) )
                $this->callbacks[$name] = [];

            $this->callbacks[$name][] = $callback;
        }

        public function dispatch( $name = null, $data = null ) {
            if( ! $name )
                throw new ORMException( __METHOD__ .' [ bad dispatch ]' );

            if( isset( $this->callbacks[$name] ) && count( $this->callbacks[$name] ) > 0 ) {
                foreach( $this->callbacks[$name] as $callbackFunction ) {
                    $callbackFunction( $data );
                }
            }
        }

    }