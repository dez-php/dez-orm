<?php

    namespace Dez\ORM;

    use Dez\Config\Config;
    use Dez\Db\Connection as PdoConnection;

    /**
     * Class Bootstrap
     * @package Dez\ORM
     */
    class Connection {

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
         * @throws Exception
         */
        static public function connect( $reconnect = false ) {

            $hash   = md5( static::$connectionName );

            if( ! isset( self::$connections[ $hash ] ) || $reconnect === true ) {

                if( static::$config->has( 'db' ) ) {
                    $config     = static::$config->get( 'db' );
                    if( $config->has( 'connection' ) ) {
                        $config     = $config->get( 'connection' );
                        if( $config->has( static::$connectionName ) ) {
                            static::$connections[ $hash ]   = new PdoConnection( $config->get( static::$connectionName ) );
                        } else {
                            throw new Exception( 'Connection configuration not found with name: '. static::$connectionName );
                        }
                    } else {
                        throw new Exception( 'Connection block not found in config' );
                    }
                } else {
                    throw new Exception( 'Db block not found in config' );
                }
            }

            return static::$connections[ $hash ];
        }

    }