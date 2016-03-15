<?php

namespace Dez\ORM;

use Dez\Config\Config;
use Dez\Db\Connection as PdoConnection;

/**
 * Class Bootstrap
 * @package Dez\ORM
 */
class Connection
{

    /** @var array() */
    static protected $connections = [];

    /** @var string */
    static protected $connectionName = 'dev';

    /** @var Config */
    static protected $config;

    /**
     * @param Config $config
     * @param null $connectionName
     */
    static public function init(Config $config, $connectionName = null)
    {
        static::$config = $config;
        static::$connectionName = $connectionName;
        self::setConnectionName($connectionName);
    }

    /**
     * @param null $connectionName
     */
    static public function setConnectionName($connectionName = null)
    {
        self::$connectionName = $connectionName;
    }

    /**
     * @param bool|false $reconnect
     * @return mixed
     * @throws Exception
     */
    static public function connect($reconnect = false)
    {

        $hash = md5(static::$connectionName);

        if (!isset(self::$connections[$hash]) || $reconnect === true) {

            if(! static::$config->has('db')) {
                throw new Exception("Invalid configuration object 'root.db.*'");
            }

            if(! static::$config['db']->has('connection')) {
                throw new Exception("Invalid configuration object 'root.db.connection.*'");
            }
            
            $name = static::$connectionName;

            if(! static::$config['db']['connection']->has($name)) {
                throw new Exception("Invalid configuration object 'root.db.connection.{$name}.*'");
            }

            static::$connections[$hash] = new PdoConnection(static::$config['db']['connection'][$name]);
        }

        return static::$connections[$hash];
    }

}