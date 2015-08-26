<?php

    namespace Dez\ORM;

    use Dez\ORM\Common\Config;
    use Dez\ORM\Exception\Error as ORMException;
    use Dez\ORM\Connection;

    class Bootstrap {

        static private
            $connections    = array(),
            $connectionName = null;

        static public function init( $configFile = null, $connectionName = null ) {

            try {
                Config::setConfig( $configFile );
            } catch ( ORMException $e ) {
                die( $e->getMessage() );
            }

            self::setConnectionName( $connectionName );
        }

        static public function setConnectionName( $connectionName = null ) {
            self::$connectionName = $connectionName;
        }

        /**
         * @return Connection\DBO $connection
        */

        static public function connect() {
            $hash   = md5( self::$connectionName );
            if( ! isset( self::$connections[ $hash ] ) ) {
                self::$connections[ $hash ] = new Connection\DBO( self::$connectionName );
            }
            return self::$connections[ $hash ];
        }

    }