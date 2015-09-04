<?php

    namespace Dez\ORM;

    use Dez\Config\Config;
    use Dez\Db\Connection;

    /**
     * Class Bootstrap
     * @package Dez\ORM
     */
    class Bootstrap {

        /** @var array() */
        static protected $connections       = [];

        /** @var string */
        static protected $connectionName    = 'dev';

        /** @var Config */
        static protected $config;

        /**
         * @param Config $config
         * @param null $connectionName
         */
        static public function init( Config $config, $connectionName = null ) {
            static::$config         = $config;
            static::$connectionName = $connectionName;
            self::setConnectionName( $connectionName );
        }

        /**
         * @param null $connectionName
         */
        static public function setConnectionName( $connectionName = null ) {
            self::$connectionName = $connectionName;
        }

        /**
         * @return Connection
         */
        static public function connect() {

            $hash   = md5( static::$connectionName );

            if( ! isset( self::$connections[ $hash ] ) ) {

                $connectionConfig   = static::$config
                    ->get( 'db' )->get( 'connection' )->get( static::$connectionName );
                $connectionConfig[ 'schema' ]   = static::$config
                    ->get( 'db' )->get( 'schema' );
                static::$connections[ $hash ]   = new Connection( $connectionConfig );

            }

            return static::$connections[ $hash ];
        }

    }