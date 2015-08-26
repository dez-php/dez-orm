<?php

    namespace Dez\ORM\Common;

    class Object {

        use ObjectTrait;

        public function __set( $name, $value ) {
            $setter = $this->setterName( $name );
            $getter = $this->getterName( $name );

            if( $this->hasMethod( $setter ) ) {
                $this->$setter( $value );
            } else if ( $this->hasMethod( $getter ) ) {
                throw new \BadMethodCallException( $this->getClassName() .'::$'. $name . ' Setting read-only property' );
            } else {
                throw new \BadMethodCallException( $this->getClassName() .'::$'. $name . ' Setting undefined property' );
            }
        }

        public function __get( $name ) {
            $setter = $this->setterName( $name );
            $getter = $this->getterName( $name );

            if( $this->hasMethod( $getter ) ) {
                return $this->$getter();
            } else if ( $this->hasMethod( $setter ) ) {
                throw new \BadMethodCallException( $this->getClassName() .'::$'. $name . ' Getting write-only property' );
            } else {
                throw new \BadMethodCallException( $this->getClassName() .'::$'. $name . ' Getting undefined property' );
            }
        }

        public function __isset( $name ) {
            $getter = $this->getterName( $name );
            return $this->hasMethod( $getter );
        }

        public function __unset( $name ) {
            $setter = $this->setterName( $name );
            $getter = $this->getterName( $name );

            if( $this->hasMethod( $setter ) ) {
                $this->$setter( null );
            } else if( $this->hasMethod( $getter ) ) {
                throw new \BadMethodCallException( $this->getClassName() .'::$'. $name .' Unsetting read-only property' );
            }
        }

        public function __call( $name, $args ) {
            throw new \BadMethodCallException( $this->getClassName() .'::'. $name .'()'. ' Calling unknown method' );
        }

        static public function __callStatic( $name, $args ) {
            throw new \BadMethodCallException( get_called_class() . '::' . $name .'()'. ' Statically call unknown method' );
        }

        public function __toString() {
            return $this->getClassName();
        }

    }