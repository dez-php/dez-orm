<?php

    namespace Dez\ORM\Common;

    use Dez\ORM\Exception\Error as ORMException;

    class Config {

        static private
            $config = array();

        public function __get( $name ) {
            return $this->get( $name );
        }

        static public function setConfig( $configFile = null ) {
            if( ! file_exists( $configFile ) ) {
                throw new ORMException( 'Config file not found ('. $configFile .')' );
            }
            $tmpArray       = parse_ini_file( $configFile, true );

            if( ! isset( $tmpArray['orm'] ) ) {
                throw new ORMException( 'Bad ORM config' );
            }

            self::$config   = self::arrayToObject( $tmpArray['orm'] ); unset( $tmpArray );
        }

        static public function getInstance() {
            static $instance;

            if( ! $instance && ! ( $instance instanceof self ) ) {
                $instance = new self;
            }

            return $instance;
        }

        public function get( $path, $default = null ) {
            if( ! $path ) return $default;

            $config = self::$config;

            foreach( explode( '.', $path ) as $chunk ) {
                if( isset( $config->{ $chunk } ) ) {
                    $config = $config->{ $chunk }; continue;
                } else {
                    return $default;
                }
            }

            return $config;
        }

        static private function arrayToObject( array $array = array() ) {
            $object = new \stdClass();
            foreach( $array as $key => $value ) {
                if( is_array( $value ) ) {
                    $object->$key = self::arrayToObject( $value );
                } else {
                    $object->$key = $value;
                }
            }
            return $object;
        }

    }