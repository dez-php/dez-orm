<?php

    namespace Dez\ORM\Common;

    trait ObjectTrait {

        public function getClassName() {
            return get_class( $this );
        }

        public function canGetProperty( $name = null ) {
            return $this->hasMethod( $this->getterName( $name ) ) && property_exists( $this, $name );
        }

        public function canSetProperty( $name = null ) {
            return $this->hasMethod( $this->setterName( $name ) ) && property_exists( $this, $name );
        }

        public function hasMethod( $methodName = null ) {
            return method_exists( $this, $methodName );
        }

        private function setterName( $propertyName = null ) {
            return 'set'. ucfirst( strtolower( $propertyName ) );
        }

        private function getterName( $propertyName = null ) {
            return 'get'. ucfirst( strtolower( $propertyName ) );
        }

    }