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
         * @param bool|false $reconnect
         * @return mixed
         */
        static public function connect( $reconnect = false ) {

            $hash   = md5( static::$connectionName );

            if( ! isset( self::$connections[ $hash ] ) || $reconnect === true ) {

                $connectionConfig   = static::$config
                    ->get( 'db' )->get( 'connection' )->get( static::$connectionName );
                $connectionConfig[ 'setting' ]  = static::$config
                    ->get( 'db' )->get( 'setting' );
                static::$connections[ $hash ]   = new Connection( $connectionConfig );

            }

            return static::$connections[ $hash ];
        }

    }